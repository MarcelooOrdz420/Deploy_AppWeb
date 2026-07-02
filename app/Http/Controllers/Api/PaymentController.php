<?php

namespace App\Http\Controllers\Api;

use App\Events\OrderStatusUpdatedForUser;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ApisPeruFacturationService;
use App\Services\Fcm\FcmClient;
use App\Services\Payments\IzipayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentController extends Controller
{
    public function izipayCheckout(Request $request, Order $order, IzipayService $izipayService): JsonResponse
    {
        if ($request->user()->role !== 'admin' && $order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if (! $this->usesIzipay($order)) {
            return response()->json(['message' => 'Este pedido no usa Izipay.'], 422);
        }

        return response()->json($izipayService->createPayment($order));
    }

    public function izipayWebhook(Request $request, IzipayService $izipayService): JsonResponse|Response
    {
        if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
            return response('OK', 200)->header('Content-Type', 'text/plain');
        }

        if (! $request->isMethod('POST')) {
            return response('OK', 200)->header('Content-Type', 'text/plain');
        }

        $payload = $izipayService->notificationPayload($request);
        $trackingCode = $izipayService->trackingCodeFromPayload($payload);

        if ($trackingCode === '') {
            return response()->json([
                'ok' => true,
                'message' => 'Izipay endpoint ready',
                'ignored' => true,
            ]);
        }

        if (! $izipayService->verifyWebhook($request)) {
            return response()->json(['ok' => false, 'message' => 'Firma Izipay invalida.'], 401);
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
        $this->trySendElectronicReceipt($order->fresh(['items']));

        return response()->json(['ok' => true]);
    }

    private function usesIzipay(Order $order): bool
    {
        return (string) $order->payment_gateway === 'izipay'
            || in_array((string) $order->payment_method, ['izipay', 'yape', 'plin'], true);
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

    private function trySendElectronicReceipt(Order $order): void
    {
        if ((string) $order->payment_status !== 'verified') {
            return;
        }

        if (! (bool) config('einvoice.auto_send', false)) {
            return;
        }

        if (! in_array((string) $order->billing_receipt_type, ['boleta', 'factura'], true)) {
            return;
        }

        try {
            app(ApisPeruFacturationService::class)->sendInvoice($order);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
