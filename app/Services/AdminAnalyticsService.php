<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminAnalyticsService
{
    public function buildOverview(?int $reservationYear = null): array
    {
        $reservationYear ??= $this->resolveReservationYear();

        return [
            'top_products' => $this->topProductsByRevenue(),
            'top_customers' => $this->topCustomersByRevenue(),
            'status_breakdown' => $this->orderStatusBreakdown(),
            'successful_sales_support' => $this->successfulSalesSupport(),
            'inventory_rotation' => $this->inventoryRotation(),
            'top_inventory_users' => $this->topInventoryUsers(),
            'top_inventory_roles' => $this->topInventoryRoles(),
            'top_may_reservations' => $this->topMayReservations($reservationYear),
            'reservation_year' => $reservationYear,
        ];
    }

    public function topProductsByRevenue(int $limit = 10): Collection
    {
        return OrderItem::query()
            ->selectRaw('
                order_items.product_id,
                MAX(order_items.product_name) as product_name,
                SUM(order_items.quantity) as units_sold,
                ROUND(SUM(order_items.line_total), 2) as revenue
            ')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', Order::STATUS_CANCELLED)
            ->where('orders.payment_status', '=', 'verified')
            ->groupBy('order_items.product_id')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();
    }

    public function topCustomersByRevenue(int $limit = 10): Collection
    {
        return Order::query()
            ->selectRaw("
                COALESCE(NULLIF(customer_email, ''), NULLIF(customer_phone, ''), CONCAT('guest:', customer_name)) as customer_key,
                MAX(customer_name) as customer_name,
                MAX(customer_phone) as customer_phone,
                MAX(customer_email) as customer_email,
                COUNT(*) as successful_orders,
                ROUND(SUM(total_amount), 2) as total_spent
            ")
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->where('payment_status', '=', 'verified')
            ->groupBy('customer_key')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get();
    }

    public function orderStatusBreakdown(): Collection
    {
        return Order::query()
            ->selectRaw('status, COUNT(*) as total_orders, ROUND(SUM(total_amount), 2) as total_amount')
            ->groupBy('status')
            ->orderByDesc('total_orders')
            ->get();
    }

    public function successfulSalesSupport(int $limit = 10): Collection
    {
        return Order::query()
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->where('payment_status', '=', 'verified')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function (Order $order): array {
                $einvoice = data_get($order->billing_metadata, 'einvoice', []);

                return [
                    'order_id' => $order->id,
                    'tracking_code' => $order->tracking_code,
                    'customer_name' => $order->billing_name ?: $order->customer_name,
                    'total_amount' => round((float) $order->total_amount, 2),
                    'payment_method' => $order->payment_method,
                    'payment_gateway' => $order->payment_gateway,
                    'payment_reference' => $order->payment_reference,
                    'payment_proof_path' => $order->payment_proof_path,
                    'receipt_type' => $order->billing_receipt_type,
                    'billing_document_number' => $order->billing_document_number,
                    'has_receipt_pdf' => true,
                    'einvoice_provider' => data_get($einvoice, 'provider'),
                    'einvoice_sent_at' => data_get($einvoice, 'sent_at'),
                    'einvoice_response_code' => data_get($einvoice, 'response.codigo'),
                ];
            });
    }

    public function inventoryRotation(int $limit = 10): Collection
    {
        if (! Schema::hasTable('inventory_movements')) {
            return collect();
        }

        return InventoryMovement::query()
            ->selectRaw("
                product_id,
                MAX(product_name_snapshot) as product_name,
                SUM(CASE WHEN direction = 'out' THEN quantity ELSE 0 END) as total_outputs,
                SUM(CASE WHEN direction = 'in' THEN quantity ELSE 0 END) as total_inputs,
                SUM(quantity) as total_rotation,
                COUNT(*) as movement_count
            ")
            ->groupBy('product_id')
            ->orderByDesc(DB::raw("SUM(CASE WHEN direction = 'out' THEN quantity ELSE 0 END)"))
            ->orderByDesc('total_rotation')
            ->limit($limit)
            ->get();
    }

    public function topInventoryUsers(int $limit = 10): Collection
    {
        if (! Schema::hasTable('inventory_movements')) {
            return collect();
        }

        return InventoryMovement::query()
            ->leftJoin('users', 'users.id', '=', 'inventory_movements.performed_by')
            ->selectRaw("
                inventory_movements.performed_by,
                COALESCE(users.name, 'Sistema / Cliente') as actor_name,
                COALESCE(users.email, '-') as actor_email,
                COUNT(*) as movement_count,
                SUM(inventory_movements.quantity) as total_units
            ")
            ->groupBy('inventory_movements.performed_by', 'users.name', 'users.email')
            ->orderByDesc('movement_count')
            ->orderByDesc('total_units')
            ->limit($limit)
            ->get();
    }

    public function topInventoryRoles(int $limit = 10): Collection
    {
        if (! Schema::hasTable('inventory_movements')) {
            return collect();
        }

        return InventoryMovement::query()
            ->selectRaw("
                COALESCE(NULLIF(role_snapshot, ''), 'sin rol') as role_name,
                COUNT(*) as movement_count,
                SUM(quantity) as total_units
            ")
            ->groupBy('role_name')
            ->orderByDesc('movement_count')
            ->orderByDesc('total_units')
            ->limit($limit)
            ->get();
    }

    public function topMayReservations(int $year, int $limit = 10): Collection
    {
        return Order::query()
            ->selectRaw("
                COALESCE(NULLIF(customer_email, ''), NULLIF(customer_phone, ''), CONCAT('guest:', customer_name)) as customer_key,
                MAX(customer_name) as customer_name,
                MAX(customer_phone) as customer_phone,
                MAX(customer_email) as customer_email,
                COUNT(*) as reservations_count,
                ROUND(SUM(total_amount), 2) as reserved_amount
            ")
            ->whereNotNull('scheduled_for')
            ->whereYear('scheduled_for', $year)
            ->whereMonth('scheduled_for', 5)
            ->groupBy('customer_key')
            ->orderByDesc('reservations_count')
            ->orderByDesc('reserved_amount')
            ->limit($limit)
            ->get();
    }

    private function resolveReservationYear(): int
    {
        $latestScheduledFor = Order::query()
            ->whereNotNull('scheduled_for')
            ->whereMonth('scheduled_for', 5)
            ->max('scheduled_for');

        if (! $latestScheduledFor) {
            return (int) now()->year;
        }

        return Carbon::parse($latestScheduledFor)->year;
    }
}
