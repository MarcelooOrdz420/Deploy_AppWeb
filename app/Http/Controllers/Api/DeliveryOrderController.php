<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryOrderController extends Controller
{
    /**
     * Pedidos con delivery, listos en cocina, todavia sin repartidor.
     * Visible para cualquier cuenta con rol "delivery" (modelo pool).
     */
    public function pool(): JsonResponse
    {
        $orders = Order::query()
            ->where('delivery_type', 'delivery')
            ->whereNull('assigned_driver_id')
            ->whereIn('status', [Order::STATUS_CONFIRMED, Order::STATUS_PREPARING])
            ->with('items')
            ->oldest()
            ->get();

        return response()->json($orders);
    }

    /**
     * Pedidos que el repartidor autenticado tiene asignados y aun no entrego.
     */
    public function mine(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->where('assigned_driver_id', $request->user()->id)
            ->where('status', Order::STATUS_ON_THE_WAY)
            ->with('items')
            ->latest()
            ->get();

        return response()->json($orders);
    }

    /**
     * Se lo asigna al repartidor que lo pide, solo si sigue libre. El
     * lockForUpdate evita que dos repartidores se queden con el mismo pedido
     * si lo tocan casi al mismo tiempo.
     */
    public function claim(Request $request, Order $order): JsonResponse
    {
        $driver = $request->user();

        $claimed = DB::transaction(function () use ($order, $driver): ?Order {
            $fresh = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ((string) $fresh->delivery_type !== 'delivery'
                || $fresh->assigned_driver_id !== null
                || ! in_array((string) $fresh->status, [Order::STATUS_CONFIRMED, Order::STATUS_PREPARING], true)) {
                return null;
            }

            $fresh->update([
                'assigned_driver_id' => $driver->id,
                'status' => Order::STATUS_ON_THE_WAY,
            ]);

            OrderStatusHistory::create([
                'order_id' => $fresh->id,
                'status' => Order::STATUS_ON_THE_WAY,
                'note' => 'Tomado por repartidor: '.$driver->name,
                'changed_by' => $driver->id,
            ]);

            return $fresh->fresh(['items']);
        });

        if (! $claimed) {
            return response()->json([
                'message' => 'Este pedido ya no esta disponible: otro repartidor se lo llevo.',
            ], 409);
        }

        app(OrderController::class)->sendOrderStatusPush($claimed, null, $driver);

        return response()->json($claimed);
    }

    /**
     * Solo permite el paso "en camino -> entregado", y solo sobre pedidos
     * que el propio repartidor autenticado tiene asignados.
     */
    public function markDelivered(Request $request, Order $order): JsonResponse
    {
        $driver = $request->user();

        if ((int) $order->assigned_driver_id !== (int) $driver->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ((string) $order->status !== Order::STATUS_ON_THE_WAY) {
            return response()->json(['message' => 'Este pedido no esta en camino.'], 422);
        }

        $order = app(OrderController::class)->applyStatusTransition(
            $order,
            Order::STATUS_DELIVERED,
            'Entregado por repartidor: '.$driver->name,
            $driver,
        );

        app(OrderController::class)->sendOrderStatusPush($order);

        return response()->json($order);
    }
}
