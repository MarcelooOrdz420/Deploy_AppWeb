<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MercadoPagoService
{
    public function isConfigured(): bool
    {
        return trim((string) config('services.mercadopago.access_token')) !== '';
    }

    public function createPreference(Order $order): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Mercado Pago no esta configurado. Falta MERCADOPAGO_ACCESS_TOKEN.');
        }

        $response = Http::withToken((string) config('services.mercadopago.access_token'))
            ->acceptJson()
            ->post('https://api.mercadopago.com/checkout/preferences', [
                'items' => $order->items->map(fn ($item): array => [
                    'id' => (string) ($item->product_id ?: $item->id),
                    'title' => (string) $item->product_name,
                    'quantity' => (int) $item->quantity,
                    'currency_id' => (string) config('company.currency', 'PEN'),
                    'unit_price' => round((float) $item->unit_price, 2),
                ])->values()->all(),
                'external_reference' => (string) $order->tracking_code,
                'statement_descriptor' => substr((string) config('company.brand_name', 'El Dorado'), 0, 16),
                'payer' => array_filter([
                    'name' => (string) $order->customer_name,
                    'email' => $order->customer_email ?: null,
                ]),
                'back_urls' => [
                    'success' => rtrim((string) config('app.url'), '/').'/mis-pedidos?mp_status=success',
                    'failure' => rtrim((string) config('app.url'), '/').'/carrito?mp_status=failure',
                    'pending' => rtrim((string) config('app.url'), '/').'/mis-pedidos?mp_status=pending',
                ],
                'auto_return' => 'approved',
                'notification_url' => rtrim((string) config('app.url'), '/').'/api/v1/payments/mercado-pago/webhook',
                'metadata' => [
                    'order_id' => $order->id,
                    'tracking_code' => $order->tracking_code,
                ],
            ]);

        $json = $response->json();
        if (! $response->ok() || ! is_array($json)) {
            throw new RuntimeException('No se pudo crear la preferencia de Mercado Pago.');
        }

        return $json;
    }

    public function fetchPayment(string|int $paymentId): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Mercado Pago no esta configurado.');
        }

        $response = Http::withToken((string) config('services.mercadopago.access_token'))
            ->acceptJson()
            ->get('https://api.mercadopago.com/v1/payments/'.urlencode((string) $paymentId));

        $json = $response->json();
        if (! $response->ok() || ! is_array($json)) {
            throw new RuntimeException('No se pudo consultar el pago en Mercado Pago.');
        }

        return $json;
    }
}
