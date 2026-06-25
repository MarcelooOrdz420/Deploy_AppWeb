<?php

namespace App\Http\Controllers\Api;

use App\Events\OrderStatusUpdatedForUser;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Fcm\FcmClient;
use App\Services\Payments\IzipayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function izipayCheckout(Request $request, Order $order, IzipayService $izipayService): JsonResponse
    {
        if ($request->user()->role !== 'admin' && $order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ((string) $order->payment_method !== 'izipay') {
            return response()->json(['message' => 'Este pedido no usa Izipay.'], 422);
        }

        return response()->json($izipayService->createPayment($order));
    }

    public function izipayWebhook(Request $request, IzipayService $izipayService): JsonResponse
    {
        if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
            return response()->json(['ok' => true, 'provider' => 'izipay']);
        }

        if (! $izipayService->isConfigured()) {
            return response()->json(['ok' => false, 'message' => 'Izipay no configurado.'], 503);
        }

        if (! $izipayService->verifyWebhook($request)) {
            return response()->json(['ok' => false, 'message' => 'Firma Izipay invalida.'], 401);
        }

        $payload = $izipayService->notificationPayload($request);
        $trackingCode = (string) (
            data_get($payload, 'orderId')
            ?: data_get($payload, 'answer.orderDetails.orderId')
            ?: data_get($payload, 'answer.orderId')
            ?: ''
        );
        if ($trackingCode === '') {
            return response()->json(['ok' => true, 'ignored' => true]);
        }

        $order = Order::query()
            ->where('tracking_code', $trackingCode)
            ->first();

        if (! $order) {
            return response()->json(['ok' => true, 'ignored' => true]);
        }

        $paymentStatus = $izipayService->paymentStatusFromPayload($payload);
        $transactionUuid = (string) (
            data_get($payload, 'transactions.0.uuid')
            ?: data_get($payload, 'answer.transactions.0.uuid')
            ?: data_get($payload, 'uuid')
            ?: $order->payment_reference
        );

        $order->forceFill([
            'payment_reference' => $transactionUuid,
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
