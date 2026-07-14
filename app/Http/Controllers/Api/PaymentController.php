<?php

namespace App\Http\Controllers\Api;

use App\Events\OrderStatusUpdatedForUser;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ElectronicInvoiceService;
use App\Services\Fcm\FcmClient;
use App\Services\Payments\IzipayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

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

        try {
            return response()->json($izipayService->createPayment($order));
        } catch (\RuntimeException $exception) {
            Log::warning('Izipay checkout rejected.', ['order_id' => $order->id, 'error' => $exception->getMessage()]);
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function izipayWebhook(Request $request, IzipayService $izipayService): JsonResponse|Response
    {
        if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
            Log::info('Izipay webhook health-check request received.', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
            ]);
            return response('OK', 200)->header('Content-Type', 'text/plain');
        }

        if (! $request->isMethod('POST')) {
            Log::warning('Izipay webhook received unsupported method.', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
            ]);
            return response('OK', 200)->header('Content-Type', 'text/plain');
        }

        // Izipay probes the notification URL with an empty POST before sending
        // signed payment data. Treat that probe as a health check.
        if (trim((string) $request->getContent()) === '') {
            Log::info('Izipay webhook empty POST health-check received.', [
                'url' => $request->fullUrl(),
            ]);

            return response('OK', 200)->header('Content-Type', 'text/plain');
        }

        Log::info('Izipay notification received.', ['url' => $request->fullUrl()]);
        if (! $izipayService->verifyWebhook($request)) {
            Log::warning('Izipay notification rejected because its signature is invalid or missing.');
            return response()->json(['ok' => false, 'message' => 'Firma Izipay invalida.'], 401);
        }
        try {
            $result = $izipayService->processNotification($request);
        } catch (\RuntimeException $exception) {
            Log::warning('Izipay notification rejected.', ['error' => $exception->getMessage()]);
            return response()->json(['ok' => false, 'message' => $exception->getMessage()], 422);
        }
        $order = $result['order'];
        if ($result['transitioned']) {
            event(new OrderStatusUpdatedForUser($order, $result['status']));
            $this->sendOrderPaymentPush($order, $result['status']);
            $this->trySendElectronicReceipt($order);
        }
        return response()->json(['ok' => true, 'status' => $result['status'], 'duplicate' => $result['duplicate']]);
    }

    public function izipayResult(Request $request, Order $order): \Illuminate\View\View
    {
        abort_unless($request->hasValidSignature(), 403);
        return view('payments.izipay-result', [
            'order' => $order,
            'statusUrl' => \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'izipay.status', now()->addMinutes(30), ['order' => $order->id]
            ),
        ]);
    }

    public function izipayStatus(Request $request, Order $order): JsonResponse
    {
        abort_unless($request->hasValidSignature(), 403);
        return response()->json(['payment_status' => $order->payment_status]);
    }

    private function usesIzipay(Order $order): bool
    {
        return (string) $order->payment_gateway === 'izipay'
            || (string) $order->payment_method === 'izipay';
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
            Log::info('Attempting automatic electronic receipt emission.', [
                'order_id' => $order->id,
                'tracking_code' => $order->tracking_code,
                'provider' => config('einvoice.provider'),
                'receipt_type' => $order->billing_receipt_type,
            ]);
            app(ElectronicInvoiceService::class)->sendInvoice($order);
        } catch (\Throwable $exception) {
            Log::error('Automatic electronic receipt emission failed.', [
                'order_id' => $order->id,
                'tracking_code' => $order->tracking_code,
                'provider' => config('einvoice.provider'),
                'error' => $exception->getMessage(),
            ]);
            report($exception);
        }
    }
}
