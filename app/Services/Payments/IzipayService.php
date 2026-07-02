<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class IzipayService
{
    public function isConfigured(): bool
    {
        return trim((string) config('services.izipay.shop_id')) !== ''
            && trim((string) config('services.izipay.rest_api_key')) !== ''
            && trim((string) config('services.izipay.public_key')) !== '';
    }

    public function createPayment(Order $order): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Izipay no esta configurado. Completa IZIPAY_SHOP_ID, IZIPAY_REST_API_KEY e IZIPAY_PUBLIC_KEY.');
        }

        $order->loadMissing(['items', 'user']);

        $payload = [
            'amount' => $this->amountInCents((float) $order->total_amount),
            'currency' => (string) config('company.currency', 'PEN'),
            'orderId' => (string) $order->tracking_code,
            'customer' => array_filter([
                'email' => $order->customer_email ?: $order->user?->email,
                'billingDetails' => array_filter([
                    'firstName' => (string) $order->customer_name,
                    'phoneNumber' => (string) $order->customer_phone,
                    'address' => (string) ($order->billing_address ?: $order->address),
                ]),
            ]),
            'metadata' => [
                'order_id' => (string) $order->id,
                'tracking_code' => (string) $order->tracking_code,
            ],
            'ipnTargetUrl' => $this->ipnTargetUrl(),
            'formAction' => 'PAYMENT',
        ];

        $response = Http::withBasicAuth(
            (string) config('services.izipay.shop_id'),
            (string) config('services.izipay.rest_api_key')
        )
            ->acceptJson()
            ->asJson()
            ->post($this->endpoint('/Charge/CreatePayment'), $payload);

        $json = $response->json();
        if (! $response->ok() || ! is_array($json)) {
            throw new RuntimeException('No se pudo iniciar el pago con Izipay.');
        }

        $formToken = data_get($json, 'answer.formToken') ?: data_get($json, 'formToken');
        if (! is_string($formToken) || trim($formToken) === '') {
            throw new RuntimeException('Izipay no devolvio un formToken valido.');
        }

        return [
            'enabled' => true,
            'form_token' => $formToken,
            'public_key' => config('services.izipay.public_key'),
            'js_url' => config('services.izipay.js_url'),
            'css_url' => config('services.izipay.css_url'),
            'payment_url' => route('izipay.checkout', [
                'order' => $order->id,
                'form_token' => $formToken,
            ]),
            'order' => [
                'id' => $order->id,
                'tracking_code' => $order->tracking_code,
                'total_amount' => $order->total_amount,
            ],
        ];
    }

    public function verifyWebhook(Request $request): bool
    {
        $hmacKey = trim((string) config('services.izipay.hmac_key'));
        if ($hmacKey === '') {
            return true;
        }

        $received = (string) (
            $request->headers->get('kr-hash')
            ?: $request->headers->get('X-KR-HASH')
            ?: $request->input('kr-hash', '')
        );

        if ($received === '') {
            return false;
        }

        $raw = (string) ($request->input('kr-answer') ?: $request->getContent());
        $expected = hash_hmac('sha256', $raw, $hmacKey);

        return hash_equals(strtolower($expected), strtolower($received));
    }

    public function notificationPayload(Request $request): array
    {
        $answer = $request->input('kr-answer');
        if (is_string($answer) && trim($answer) !== '') {
            $decoded = json_decode($answer, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return $request->all();
    }

    public function trackingCodeFromPayload(array $payload): string
    {
        return trim((string) (
            data_get($payload, 'orderId')
            ?: data_get($payload, 'answer.orderDetails.orderId')
            ?: data_get($payload, 'answer.orderId')
            ?: data_get($payload, 'orderDetails.orderId')
            ?: data_get($payload, 'metadata.tracking_code')
            ?: data_get($payload, 'answer.metadata.tracking_code')
            ?: ''
        ));
    }

    public function paymentStatusFromPayload(array $payload): string
    {
        $status = strtolower((string) (
            data_get($payload, 'orderStatus')
            ?: data_get($payload, 'answer.orderStatus')
            ?: data_get($payload, 'transactionStatus')
            ?: data_get($payload, 'answer.transactionStatus')
            ?: ''
        ));

        return match ($status) {
            'paid', 'accepted', 'captured', 'authorised', 'authorized' => 'verified',
            'refused', 'cancelled', 'canceled', 'failed', 'error', 'unpaid' => 'rejected',
            default => 'pending',
        };
    }

    private function amountInCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    private function endpoint(string $path): string
    {
        return rtrim((string) config('services.izipay.api_base_url'), '/').'/'.ltrim($path, '/');
    }

    private function ipnTargetUrl(): string
    {
        $configured = trim((string) config('services.izipay.ipn_url', ''));

        if ($configured !== '') {
            return $configured;
        }

        return route('izipay.ipn');
    }
}
