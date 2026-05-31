<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function culqiCheckout(Request $request, Order $order): JsonResponse
    {
        if ($request->user()->role !== 'admin' && $order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return response()->json([
            'mode' => config('services.culqi.mode', 'test'),
            'enabled' => (bool) config('services.culqi.enabled', false),
            'reference_only' => (bool) config('services.culqi.reference_only', true),
            'public_key' => config('services.culqi.public_key'),
            'rsa_id' => config('services.culqi.rsa_id'),
            'rsa_public_key' => config('services.culqi.rsa_public_key'),
            'amount' => (int) round((float) $order->total_amount * 100),
            'currency_code' => config('company.currency', 'PEN'),
            'order' => [
                'id' => $order->id,
                'tracking_code' => $order->tracking_code,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'billing_document_type' => $order->billing_document_type,
                'billing_document_number' => $order->billing_document_number,
                'billing_receipt_type' => $order->billing_receipt_type,
            ],
            'metadata' => [
                'integration' => 'culqi_reference',
                'tracking_code' => $order->tracking_code,
                'receipt_type' => $order->billing_receipt_type,
            ],
        ]);
    }
}
