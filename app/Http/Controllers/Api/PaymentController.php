<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
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
        $startedAt = microtime(true);

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
        if (trim((string) $request->getContent()) === ''
            && $request->request->count() === 0
            && ! $izipayService->hasWebhookAnswer($request)) {
            Log::info('Izipay webhook empty POST health-check received.', [
                'url' => $request->fullUrl(),
            ]);

            return response('OK', 200)->header('Content-Type', 'text/plain');
        }

        Log::info('Izipay IPN received.', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'has_kr_answer' => $izipayService->hasWebhookAnswer($request),
        ]);
        if (! $izipayService->verifyWebhook($request)) {
            Log::warning('Izipay signature invalid.', [
                'hmac_valid' => false,
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);

            return response('Invalid signature', 401)->header('Content-Type', 'text/plain');
        }

        Log::info('Izipay signature valid.', ['hmac_valid' => true]);

        if ($izipayService->notificationPayload($request) === []) {
            Log::warning('Izipay payload is not valid JSON.');

            return response('Invalid payload', 400)->header('Content-Type', 'text/plain');
        }

        try {
            $result = $izipayService->processNotification($request);
        } catch (\RuntimeException $exception) {
            Log::warning('Izipay IPN rejected.', [
                'error' => $exception->getMessage(),
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);

            return response('Invalid payload', 400)->header('Content-Type', 'text/plain');
        } catch (\Throwable $exception) {
            Log::error('Izipay IPN exception.', [
                'error' => $exception->getMessage(),
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);
            report($exception);

            return response('Internal error', 500)->header('Content-Type', 'text/plain');
        }

        $order = $result['order'];

        Log::info('Izipay IPN processed.', [
            'order_id' => $order->id,
            'tracking_code' => $order->tracking_code,
            'status' => $result['status'],
            'duplicate' => $result['duplicate'],
            'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
        ]);

        return response('OK', 200)->header('Content-Type', 'text/plain');
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

}
