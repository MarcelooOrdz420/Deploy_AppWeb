<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\IssueElectronicReceiptAfterVerifiedPayment;
use App\Models\Order;
use App\Models\Product;
use App\Services\InventoryMovementService;
use App\Services\Payments\IzipayService;
use App\Services\Payments\IzipayPaymentConfirmationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
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

    public function izipayWebhook(
        Request $request,
        IzipayService $izipayService,
        IzipayPaymentConfirmationService $confirmationService
    ): JsonResponse|Response
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

        if (config('services.izipay.require_relay')) {
            $expectedSecret = (string) config('services.izipay.relay_secret');
            $receivedSecret = (string) $request->header('X-Relay-Secret', '');
            if ($expectedSecret === '' || $receivedSecret === '' || ! hash_equals($expectedSecret, $receivedSecret)) {
                Log::warning('Izipay relay authentication rejected.', [
                    'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
                ]);
                return response('Unauthorized relay', 401)->header('Content-Type', 'text/plain');
            }
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
        try {
            $fields = $izipayService->webhookFields($request);
            $this->logIzipaySignatureMetadata($request, $fields, 'ipn');
            $result = $confirmationService->confirm(
                $fields['answer'], $fields['hash'], $fields['algorithm'], null, $fields['hash_key'], 'ipn',
                $this->signatureDiagnostics($request, $fields)
            );
        } catch (\RuntimeException $exception) {
            Log::warning('Izipay IPN rejected.', [
                'error' => $exception->getMessage(),
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);

            $invalidSignature = $exception->getMessage() === 'Firma Izipay invalida.';
            return response($invalidSignature ? 'Invalid signature' : 'Invalid payload', $invalidSignature ? 401 : 400)
                ->header('Content-Type', 'text/plain');
        } catch (\Throwable $exception) {
            Log::error('Izipay IPN exception.', [
                'error' => $exception->getMessage(),
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);
            report($exception);

            return response('Internal error', 500)->header('Content-Type', 'text/plain');
        }

        $order = $result['order'];
        if ($result['status'] === 'verified') {
            IssueElectronicReceiptAfterVerifiedPayment::dispatchAfterResponse($order->id);
        }

        Log::info('Izipay IPN processed.', [
            'order_id' => $order->id,
            'tracking_code' => $order->tracking_code,
            'status' => $result['status'],
            'duplicate' => $result['duplicate'],
            'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
        ]);

        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

    public function izipayResult(
        Request $request,
        Order $order,
        IzipayService $izipayService,
        IzipayPaymentConfirmationService $confirmationService
    ): \Illuminate\View\View
    {
        abort_unless($request->hasValidSignature(), 403);
        $confirmationError = null;
        if ($request->isMethod('POST') && $izipayService->hasWebhookAnswer($request)) {
            try {
                $fields = $izipayService->webhookFields($request);
                $this->logIzipaySignatureMetadata($request, $fields, 'browser_return');
                $result = $confirmationService->confirm(
                    $fields['answer'], $fields['hash'], $fields['algorithm'], $order->id,
                    $fields['hash_key'], 'browser_return', $this->signatureDiagnostics($request, $fields)
                );
                if ($result['status'] === 'verified') {
                    IssueElectronicReceiptAfterVerifiedPayment::dispatchAfterResponse($result['order']->id);
                }
                $order->refresh();
            } catch (\RuntimeException $exception) {
                $confirmationError = 'No se pudo validar la respuesta firmada de Izipay.';
                Log::warning('Izipay browser return rejected.', ['order_id' => $order->id, 'error' => $exception->getMessage()]);
            }
        }
        return view('payments.izipay-result', [
            'order' => $order,
            'isMobileClient' => (bool) preg_match('/Android|iPhone|iPad|iPod/i', (string) $request->userAgent()),
            'confirmationError' => $confirmationError,
            'statusUrl' => \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'izipay.status', now()->addMinutes(30), ['order' => $order->id]
            ),
            'cancelUrl' => \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'izipay.cancel-unpaid', now()->addMinutes(30), ['order' => $order->id]
            ),
        ]);
    }

    public function izipayStatus(Request $request, Order $order): JsonResponse
    {
        abort_unless($request->hasValidSignature(), 403);
        return response()->json(['payment_status' => $order->payment_status]);
    }

    public function izipayCancelUnpaid(Request $request, Order $order): JsonResponse
    {
        abort_unless($request->hasValidSignature(), 403);

        if (! $this->usesIzipay($order)
            || ! in_array((string) $order->payment_status, ['pending', 'rejected'], true)
            || (string) $order->status !== Order::STATUS_PENDING) {
            return response()->json([
                'message' => 'Este pedido ya no se puede cancelar.',
            ], 422);
        }

        DB::transaction(function () use ($order): void {
            // Se vuelve a leer con bloqueo dentro de la transaccion: si el
            // webhook de Izipay verifico el pago justo en este instante, no
            // se debe borrar un pedido que ya fue cobrado.
            $fresh = Order::query()->lockForUpdate()->findOrFail($order->id);
            if (! $this->usesIzipay($fresh)
                || ! in_array((string) $fresh->payment_status, ['pending', 'rejected'], true)
                || (string) $fresh->status !== Order::STATUS_PENDING) {
                abort(response()->json([
                    'message' => 'Este pedido ya no se puede cancelar.',
                ], 422));
            }

            $order = $fresh;
            $order->load('items');
            $movementService = app(InventoryMovementService::class);

            foreach ($order->items as $item) {
                if (! $item->product_id) {
                    continue;
                }

                $product = Product::query()->lockForUpdate()->find($item->product_id);
                if (! $product) {
                    continue;
                }

                $stockBefore = (int) $product->stock;
                $product->increment('stock', (int) $item->quantity);
                $product->refresh();

                $movementService->logCancellationReturn(
                    order: $order,
                    product: $product,
                    quantity: (int) $item->quantity,
                    stockBefore: $stockBefore,
                    stockAfter: (int) $product->stock,
                    actor: $order->user,
                );
            }

            $order->delete();
        });

        return response()->json(['message' => 'Pedido cancelado, stock repuesto.']);
    }

    private function usesIzipay(Order $order): bool
    {
        return (string) $order->payment_gateway === 'izipay'
            && (string) $order->payment_method === 'izipay';
    }

    /** @param array{answer:string,hash:string,algorithm:string,hash_key:string} $fields */
    private function logIzipaySignatureMetadata(Request $request, array $fields, string $source): void
    {
        Log::info('Izipay signature metadata', [
            'source' => $source,
            'algorithm' => $fields['algorithm'],
            'hash_key' => $fields['hash_key'],
            'kr_answer_length' => strlen($fields['answer']),
            'kr_hash_length' => strlen($fields['hash']),
            'hmac_key_length' => strlen((string) config('services.izipay.hmac_key')),
            'content_type' => $request->header('Content-Type'),
        ]);
    }

    /** @param array{answer:string,hash:string,algorithm:string,hash_key:string} $fields */
    private function signatureDiagnostics(Request $request, array $fields): array
    {
        $rawFields = [];
        parse_str((string) $request->getContent(), $rawFields);

        return [
            'content_type' => $request->header('Content-Type'),
            'form_value_matches_raw_body' => array_key_exists('kr-answer', $rawFields)
                && is_string($rawFields['kr-answer'])
                && hash_equals($fields['answer'], $rawFields['kr-answer']),
            'relay_received' => $request->hasHeader('X-Izipay-Relay'),
        ];
    }

}
