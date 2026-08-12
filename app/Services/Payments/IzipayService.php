<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use RuntimeException;

class IzipayService
{
    public function isConfigured(): bool
    {
        return (bool) config('services.izipay.enabled')
            && collect(['shop_id', 'rest_api_key', 'public_key', 'hmac_key'])
                ->every(fn (string $key): bool => trim((string) config("services.izipay.{$key}")) !== '');
    }

    public function createPayment(Order $order): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Izipay no esta configurado. Completa las credenciales REST, publica y HMAC.');
        }
        if (! $this->usesIzipay($order) || $order->status === Order::STATUS_CANCELLED) {
            throw new RuntimeException('El pedido no esta habilitado para pagar con Izipay.');
        }
        if ($order->payment_status === 'verified') {
            throw new RuntimeException('Este pedido ya fue pagado.');
        }

        $order->loadMissing(['items', 'user']);
        $amountCents = $this->decimalToCents((string) $order->total_amount);
        if ($amountCents <= 0 || trim((string) $order->tracking_code) === '') {
            throw new RuntimeException('El total almacenado del pedido no es valido.');
        }

        $currency = strtoupper((string) config('company.currency', 'PEN'));
        if ($currency !== 'PEN') {
            throw new RuntimeException('Izipay solo esta habilitado para pagos en PEN.');
        }
        $reference = (string) $order->tracking_code;
        $ipnUrl = $this->ipnTargetUrl();
        $this->ensureValidIpnTargetUrl($ipnUrl);
        $payment = PaymentTransaction::query()->create([
            'order_id' => $order->id, 'provider' => 'izipay', 'status' => 'pending',
            'amount' => $amountCents / 100, 'currency' => $currency,
            'merchant_order_id' => $reference,
        ]);

        $payload = [
            'amount' => $amountCents, 'currency' => $currency,
            'orderId' => $reference, 'formAction' => 'PAYMENT', 'ipnTargetUrl' => $ipnUrl,
            'customer' => array_filter([
                'email' => $order->customer_email ?: $order->user?->email,
                'billingDetails' => array_filter(['firstName' => $order->customer_name,
                    'phoneNumber' => $order->customer_phone,
                    'address' => $order->billing_address ?: $order->address]),
            ]),
            'metadata' => ['order_id' => (string) $order->id, 'tracking_code' => $reference],
        ];

        Log::info('Izipay payment creation started.', ['order_id' => $order->id,
            'reference' => $reference, 'amount' => $payload['amount'], 'currency' => $currency]);
        try {
            $response = Http::withBasicAuth((string) config('services.izipay.shop_id'),
                (string) config('services.izipay.rest_api_key'))
                ->timeout((int) config('services.izipay.timeout', 15))->retry(2, 250, throw: false)->acceptJson()->asJson()
                ->post($this->endpoint('/Charge/CreatePayment'), $payload);
        } catch (\Throwable $e) {
            $payment->update(['status' => 'rejected', 'response_message' => 'Error de conexion con Izipay']);
            Log::error('Izipay connection error.', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            throw new RuntimeException('No se pudo conectar con Izipay.', previous: $e);
        }
        $json = $response->json();
        $formToken = is_array($json) ? (data_get($json, 'answer.formToken') ?: data_get($json, 'formToken')) : null;
        if (! $response->successful() || ! is_string($formToken) || trim($formToken) === '') {
            $payment->update(['status' => 'rejected', 'response_code' => (string) $response->status(),
                'response_message' => 'Izipay no devolvio un formToken valido']);
            throw new RuntimeException('No se pudo iniciar el pago con Izipay.');
        }
        $payment->update(['form_token_reference' => $formToken, 'status' => 'pending']);
        Log::info('Izipay form token created.', ['order_id' => $order->id, 'reference' => $reference]);

        return ['success' => true, 'orderId' => $reference,
            'payment_url' => URL::temporarySignedRoute('izipay.checkout', now()->addMinutes(20), ['order' => $order->id])];
    }

    public function verifyWebhook(Request $request): bool
    {
        $fields = $this->webhookFields($request);
        $key = match (strtolower($fields['hash_key'])) {
            'sha256_hmac', 'hmac-sha-256' => (string) config('services.izipay.hmac_key'),
            'password' => (string) config('services.izipay.rest_api_key'),
            default => '',
        };
        $received = trim($fields['hash']);
        $raw = $fields['answer'];
        if ($key === '' || $received === '' || $raw === ''
            || strcasecmp($fields['algorithm'], 'sha256_hmac') !== 0
            || $key !== trim($key)) {
            return false;
        }

        return hash_equals(strtolower(hash_hmac('sha256', $raw, $key)), strtolower($received));
    }

    public function hasWebhookAnswer(Request $request): bool
    {
        return $this->webhookFields($request)['answer'] !== '';
    }

    public function notificationPayload(Request $request): array
    {
        $answer = $this->webhookFields($request)['answer'];
        $decoded = json_decode($answer, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function processNotification(Request $request): array
    {
        if (! $this->verifyWebhook($request)) {
            throw new RuntimeException('Firma Izipay invalida.');
        }
        $payload = $this->notificationPayload($request);
        $reference = $this->trackingCodeFromPayload($payload);
        if ($reference === '') {
            throw new RuntimeException('Referencia Izipay ausente.');
        }

        return DB::transaction(function () use ($payload, $reference): array {
            $order = Order::query()->where('tracking_code', $reference)->lockForUpdate()->first();
            if (! $order || ! $this->usesIzipay($order)) {
                throw new RuntimeException('Pedido Izipay no encontrado.');
            }
            if ($order->status === Order::STATUS_CANCELLED) {
                throw new RuntimeException('El pedido Izipay esta cancelado.');
            }
            $payment = PaymentTransaction::query()->where('order_id', $order->id)
                ->where('provider', 'izipay')->where('merchant_order_id', $reference)
                ->latest('id')->lockForUpdate()->first();
            if (! $payment) {
                throw new RuntimeException('Intento de pago no encontrado.');
            }
            $shopId = trim((string) (data_get($payload, 'shopId') ?: data_get($payload, 'orderDetails.shopId')));
            $amount = data_get($payload, 'orderDetails.amount') ?? data_get($payload, 'amount');
            $currency = strtoupper(trim((string) (data_get($payload, 'orderDetails.currency') ?: data_get($payload, 'currency'))));
            $transactionId = trim((string) (data_get($payload, 'transactions.0.uuid') ?: data_get($payload, 'transactionDetails.cardDetails.legacyTransId') ?: data_get($payload, 'uuid')));
            if ($shopId !== trim((string) config('services.izipay.shop_id')) || ! is_numeric($amount)
                || (int) $amount !== $this->decimalToCents((string) $order->total_amount)
                || $currency !== 'PEN' || strtoupper((string) $payment->currency) !== 'PEN' || $transactionId === '') {
                throw new RuntimeException('Los datos de la transaccion no coinciden con el pedido.');
            }
            $existing = PaymentTransaction::query()->where('transaction_uuid', $transactionId)->lockForUpdate()->first();
            if ($existing && $existing->id !== $payment->id) {
                if ($existing->order_id === $order->id && $existing->status === 'verified') {
                    return ['order' => $order, 'status' => 'verified', 'duplicate' => true, 'transitioned' => false];
                }
                throw new RuntimeException('La transaccion ya pertenece a otro intento o pedido.');
            }
            $status = $this->paymentStatusFromPayload($payload);
            if ($payment->status === 'verified' && $order->payment_status === 'verified') {
                Log::info('Duplicate Izipay notification ignored.', ['order_id' => $order->id, 'transaction_id' => $transactionId]);

                return ['order' => $order, 'status' => 'verified', 'duplicate' => true, 'transitioned' => false];
            }
            $wasVerified = $order->payment_status === 'verified';
            $effectiveStatus = $wasVerified ? 'verified' : $status;
            $paymentStatus = $payment->status === 'verified' ? 'verified' : $status;
            $payment->update(['status' => $paymentStatus, 'transaction_uuid' => $transactionId,
                'authorization_number' => data_get($payload, 'transactions.0.transactionDetails.cardDetails.authorizationResponse.authorizationResult'),
                'response_code' => (string) (data_get($payload, 'transactions.0.responseCode') ?: data_get($payload, 'transactions.0.detailedStatus') ?: data_get($payload, 'orderStatus')),
                'response_message' => (string) (data_get($payload, 'transactions.0.responseMessage') ?: data_get($payload, 'transactions.0.errorMessage') ?: data_get($payload, 'orderStatus')),
                'raw_response' => $this->sanitizePayload($payload), 'processed_at' => now()]);
            $order->forceFill(['payment_gateway' => $order->payment_gateway ?: 'izipay',
                'payment_reference' => $transactionId, 'payment_status' => $effectiveStatus,
                'payment_reported_at' => $order->payment_reported_at ?: now(),
                'payment_verified_at' => $effectiveStatus === 'verified' ? ($order->payment_verified_at ?: now()) : $order->payment_verified_at])->save();
            Log::info('Izipay notification processed.', ['order_id' => $order->id, 'status' => $status, 'transaction_id' => $transactionId]);

            return ['order' => $order->fresh(['items', 'statusHistory']), 'status' => $effectiveStatus,
                'duplicate' => false, 'transitioned' => ! $wasVerified && $effectiveStatus === 'verified'];
        });
    }

    public function trackingCodeFromPayload(array $payload): string
    {
        return trim((string) (data_get($payload, 'orderDetails.orderId') ?: data_get($payload, 'orderId') ?: data_get($payload, 'metadata.tracking_code')));
    }

    public function paymentStatusFromPayload(array $payload): string
    {
        $status = strtoupper((string) (data_get($payload, 'orderStatus') ?: data_get($payload, 'transactions.0.status') ?: data_get($payload, 'transactions.0.detailedStatus')));

        return match ($status) {
            'PAID', 'ACCEPTED', 'CAPTURED', 'AUTHORISED', 'AUTHORIZED' => 'verified',
            'REFUSED', 'CANCELLED', 'CANCELED', 'FAILED', 'ERROR', 'UNPAID' => 'rejected',
            default => 'pending',
        };
    }

    private function sanitizePayload(array $payload): array
    {
        $sensitive = ['card', 'cardnumber', 'pan', 'cvv', 'formtoken', 'expirydate', 'expirationdate'];
        foreach ($payload as $key => $value) {
            $normalizedKey = strtolower(str_replace(['-', '_'], '', (string) $key));
            if (in_array($normalizedKey, $sensitive, true)) {
                unset($payload[$key]);
            } elseif (is_array($value)) {
                $payload[$key] = $this->sanitizePayload($value);
            }
        }

        return $payload;
    }

    /** @return array{answer:string,hash:string,algorithm:string,hash_key:string} */
    public function webhookFields(Request $request): array
    {
        $form = $request->request->all();
        if ($form === [] && str_contains(strtolower((string) $request->header('Content-Type')), 'application/x-www-form-urlencoded')) {
            parse_str((string) $request->getContent(), $form);
        }

        return [
            'answer' => (string) ($form['kr-answer'] ?? $request->header('X-KR-ANSWER') ?? $request->header('kr-answer', '')),
            'hash' => (string) ($form['kr-hash'] ?? $request->header('X-KR-HASH') ?? $request->header('kr-hash', '')),
            'algorithm' => (string) ($form['kr-hash-algorithm'] ?? $request->header('X-KR-HASH-ALGORITHM') ?? $request->header('kr-hash-algorithm', '')),
            'hash_key' => (string) ($form['kr-hash-key'] ?? $request->header('X-KR-HASH-KEY') ?? $request->header('kr-hash-key', '')),
        ];
    }

    private function usesIzipay(Order $order): bool
    {
        return $order->payment_gateway === 'izipay' || $order->payment_method === 'izipay';
    }

    private function decimalToCents(string $amount): int
    {
        $normalized = str_replace(',', '.', trim($amount));
        if (! preg_match('/^\d+(?:\.\d{1,2})?$/', $normalized)) {
            return 0;
        }
        [$whole, $decimal] = array_pad(explode('.', $normalized, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad($decimal, 2, '0');
    }

    private function endpoint(string $path): string
    {
        return rtrim((string) config('services.izipay.api_base_url'), '/').'/'.ltrim($path, '/');
    }

    public function ipnTargetUrl(): string
    {
        return trim((string) config('services.izipay.ipn_url')) ?: route('izipay.ipn');
    }

    private function ensureValidIpnTargetUrl(string $url): void
    {
        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        if (! filter_var($url, FILTER_VALIDATE_URL) || strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || $host === '' || in_array($host, ['localhost', '127.0.0.1', '0.0.0.0', '::1'], true)
            || Str::endsWith($host, ['.local', '.internal', '.localhost'])) {
            throw new RuntimeException('La URL IPN de Izipay debe ser HTTPS y publica.');
        }
    }
}
