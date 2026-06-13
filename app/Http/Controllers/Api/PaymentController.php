<?php

namespace App\Http\Controllers\Api;

use App\Events\OrderStatusUpdatedForUser;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Fcm\FcmClient;
use App\Services\Payments\MercadoPagoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function mercadoPagoCheckout(Request $request, Order $order, MercadoPagoService $mercadoPagoService): JsonResponse
    {
        if ($request->user()->role !== 'admin' && $order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ((string) $order->payment_method !== 'mercado_pago') {
            return response()->json(['message' => 'Este pedido no usa Mercado Pago.'], 422);
        }

        $preference = $mercadoPagoService->createPreference($order->loadMissing('items'));

        return response()->json([
            'enabled' => (bool) config('services.mercadopago.enabled', false),
            'public_key' => config('services.mercadopago.public_key'),
            'currency_code' => config('company.currency', 'PEN'),
            'checkout_url' => $preference['init_point'] ?? null,
            'sandbox_checkout_url' => $preference['sandbox_init_point'] ?? null,
            'preference_id' => $preference['id'] ?? null,
            'order' => [
                'id' => $order->id,
                'tracking_code' => $order->tracking_code,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
            ],
        ]);
    }

    public function mercadoPagoWebhook(Request $request, MercadoPagoService $mercadoPagoService): JsonResponse
    {
        if (! $mercadoPagoService->isConfigured()) {
            return response()->json(['ok' => false, 'message' => 'Mercado Pago no configurado.'], 503);
        }

        $type = Str::lower((string) ($request->input('type') ?? $request->input('topic') ?? ''));
        $paymentId = $request->input('data.id') ?? $request->input('id');

        if (! in_array($type, ['payment'], true) || ! $paymentId) {
            return response()->json(['ok' => true, 'ignored' => true]);
        }

        $payment = $mercadoPagoService->fetchPayment((string) $paymentId);
        $trackingCode = (string) ($payment['external_reference'] ?? '');
        if ($trackingCode === '') {
            return response()->json(['ok' => true, 'ignored' => true]);
        }

        $order = Order::query()
            ->where('tracking_code', $trackingCode)
            ->first();

        if (! $order) {
            return response()->json(['ok' => true, 'ignored' => true]);
        }

        $status = Str::lower((string) ($payment['status'] ?? 'pending'));
        $paymentStatus = match ($status) {
            'approved' => 'verified',
            'rejected', 'cancelled', 'refunded', 'charged_back' => 'rejected',
            'in_process', 'pending', 'authorized' => 'pending',
            default => 'pending',
        };

        $order->forceFill([
            'payment_reference' => (string) ($payment['id'] ?? $order->payment_reference),
            'payment_status' => $paymentStatus,
            'payment_verified_at' => $paymentStatus === 'verified' ? now() : null,
        ])->save();

        event(new OrderStatusUpdatedForUser($order->fresh(['items', 'statusHistory']), $paymentStatus));
        $this->sendOrderPaymentPush($order, $paymentStatus);

        return response()->json(['ok' => true]);
    }

    private function sendOrderPaymentPush(Order $order, string $paymentStatus): void
    {
        try {
            $userId = (int) $order->user_id;
            if ($userId <= 0) {
                return;
            }

            /** @var FcmClient $client */
            $client = app(FcmClient::class);
            if (! $client->isConfigured()) {
                return;
            }

            $tracking = (string) ($order->tracking_code ?? '');
            $status = (string) ($order->status ?? '');
            $body = $tracking !== ''
                ? "Pedido {$tracking}: {$status} | Pago: {$paymentStatus}"
                : "Pago actualizado: {$paymentStatus}";

            $client->sendToTopic(
                topic: "orders_user_{$userId}",
                notification: [
                    'title' => 'Actualizacion de pedido',
                    'body' => $body,
                ],
                data: [
                    'route' => '/orders',
                    'tracking_code' => $tracking,
                    'status' => $status,
                    'payment_status' => $paymentStatus,
                ],
            );
        } catch (\Throwable) {
            // No romper webhook por falla de push.
        }
    }
}
