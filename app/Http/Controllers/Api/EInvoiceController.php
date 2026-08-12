<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ElectronicInvoiceService;
use App\Services\ElectronicReceiptDeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class EInvoiceController extends Controller
{
    public function preview(Request $request, Order $order, ElectronicInvoiceService $service): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        try {
            return response()->json($service->previewPayload($order));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function send(Request $request, Order $order, ElectronicInvoiceService $service): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        if ((string) $order->payment_status !== 'verified') {
            return response()->json(['message' => 'El pago debe estar verificado antes de emitir el comprobante.'], 422);
        }
        if ((string) $order->payment_method === 'cod' && (string) $order->status !== Order::STATUS_DELIVERED) {
            return response()->json(['message' => 'Confirma la entrega antes de emitir un comprobante contraentrega.'], 422);
        }

        try {
            return response()->json($service->sendInvoice($order, force: true));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function sendCustomerCopy(
        Request $request,
        Order $order,
        ElectronicReceiptDeliveryService $deliveryService
    ): JsonResponse {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        try {
            return response()->json($deliveryService->sendCustomerCopy($order));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
