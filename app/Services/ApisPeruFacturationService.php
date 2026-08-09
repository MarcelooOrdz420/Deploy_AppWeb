<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ApisPeruFacturationService
{
    public function __construct(
        private readonly SpanishAmountService $amountService,
    ) {
    }

    public function sendInvoice(Order $order, bool $force = false): array
    {
        $existing = data_get($order->billing_metadata, 'einvoice.response');
        if (! $force && is_array($existing) && ! empty(data_get($order->billing_metadata, 'einvoice.sent_at'))) {
            return [
                'ok' => true,
                'already_sent' => true,
                'response' => $existing,
            ];
        }

        $token = $this->authToken();
        $payload = $this->buildPayload($order);

        $response = Http::baseUrl(rtrim((string) config('services.apisperu_fact.base_url'), '/'))
            ->timeout((int) config('services.apisperu_fact.timeout', 30))
            ->withToken($token)
            ->acceptJson()
            ->post('/invoice/send', $payload);

        if ($response->failed()) {
            throw new RuntimeException('No se pudo emitir el comprobante electronico.');
        }

        $data = $response->json();

        $metadata = $order->billing_metadata ?? [];
        $metadata['einvoice'] = [
            'provider' => 'apisperu',
            'payload' => $payload,
            'response' => $data,
            'sent_at' => now()->toIso8601String(),
            'last_attempt_at' => now()->toIso8601String(),
            'status' => 'sent',
        ];

        $order->update([
            'billing_metadata' => $metadata,
        ]);

        return $data;
    }

    public function previewPayload(Order $order): array
    {
        return $this->buildPayload($order);
    }

    private function authToken(): string
    {
        $companyToken = trim((string) config('services.apisperu_fact.company_token'));
        if ($companyToken !== '') {
            return $companyToken;
        }

        $username = trim((string) config('services.apisperu_fact.username'));
        $password = trim((string) config('services.apisperu_fact.password'));
        if ($username === '' || $password === '') {
            throw new RuntimeException('Configura el token de empresa o las credenciales de APIsPeru Facturacion.');
        }

        return Cache::remember('apisperu_fact_auth_token', now()->addHours(23), function () use ($username, $password): string {
            $response = Http::baseUrl(rtrim((string) config('services.apisperu_fact.base_url'), '/'))
                ->timeout((int) config('services.apisperu_fact.timeout', 30))
                ->acceptJson()
                ->post('/auth/login', [
                    'username' => $username,
                    'password' => $password,
                ]);

            if ($response->failed()) {
                throw new RuntimeException('No se pudo autenticar con APIsPeru Facturacion.');
            }

            $token = (string) data_get($response->json(), 'token', '');
            if ($token === '') {
                throw new RuntimeException('APIsPeru Facturacion no devolvio token.');
            }

            return $token;
        });
    }

    private function buildPayload(Order $order): array
    {
        $order->loadMissing(['items', 'user']);

        $receiptType = $order->billing_receipt_type;
        if (! in_array($receiptType, ['boleta', 'factura'], true)) {
            throw new RuntimeException('El pedido no tiene un tipo de comprobante valido.');
        }

        $currency = (string) config('einvoice.currency', 'PEN');
        $this->ensureCompanyCanInvoice();

        $series = $receiptType === 'factura'
            ? (string) config('einvoice.factura_series', 'F001')
            : (string) config('einvoice.boleta_series', 'B001');
        $tipoDoc = $receiptType === 'factura' ? '01' : '03';
        $clientDocType = $order->billing_document_type === 'ruc' ? '6' : '1';

        $items = $order->items()->get();
        $taxedBase = round((float) $order->total_amount / 1.18, 2);
        $igv = round((float) $order->total_amount - $taxedBase, 2);

        $details = $items->values()->map(function ($item, $index): array {
            $lineTotal = round((float) $item->line_total, 2);
            $lineBase = round($lineTotal / 1.18, 2);
            $lineIgv = round($lineTotal - $lineBase, 2);
            $quantity = (int) $item->quantity;
            $valueUnit = $quantity > 0 ? round($lineBase / $quantity, 2) : 0;
            $priceUnit = $quantity > 0 ? round($lineTotal / $quantity, 2) : 0;

            return [
                'codProducto' => 'P'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'unidad' => 'NIU',
                'descripcion' => $item->product_name,
                'cantidad' => $quantity,
                'mtoValorUnitario' => $valueUnit,
                'mtoValorVenta' => $lineBase,
                'mtoBaseIgv' => $lineBase,
                'porcentajeIgv' => 18,
                'igv' => $lineIgv,
                'tipAfeIgv' => 10,
                'totalImpuestos' => $lineIgv,
                'mtoPrecioUnitario' => $priceUnit,
            ];
        })->all();

        return [
            'ublVersion' => '2.1',
            'tipoOperacion' => '0101',
            'tipoDoc' => $tipoDoc,
            'serie' => $series,
            'correlativo' => (string) $order->id,
            'fechaEmision' => optional($order->created_at)->setTimezone('America/Lima')->format('Y-m-d\TH:i:sP'),
            'formaPago' => [
                'moneda' => $currency,
                'tipo' => 'Contado',
            ],
            'tipoMoneda' => $currency,
            'client' => [
                'tipoDoc' => $clientDocType,
                'numDoc' => $order->billing_document_number,
                'rznSocial' => $order->billing_name ?: $order->customer_name,
                'email' => $order->billing_email ?: $order->customer_email ?: $order->user?->email,
                'telephone' => $order->customer_phone,
                'address' => [
                    'direccion' => $order->billing_address ?: $order->address ?: '-',
                    'provincia' => data_get($order->billing_metadata, 'normalized.province', 'LIMA'),
                    'departamento' => data_get($order->billing_metadata, 'normalized.department', 'LIMA'),
                    'distrito' => data_get($order->billing_metadata, 'normalized.district', 'LIMA'),
                    'ubigueo' => data_get($order->billing_metadata, 'normalized.ubigeo', '150101'),
                ],
            ],
            'company' => [
                'ruc' => trim((string) config('einvoice.company.ruc')),
                'razonSocial' => trim((string) config('einvoice.company.razon_social')),
                'nombreComercial' => trim((string) config('einvoice.company.nombre_comercial')),
                'email' => trim((string) config('einvoice.company.email')),
                'telephone' => trim((string) config('einvoice.company.telephone')),
                'address' => [
                    'direccion' => trim((string) config('einvoice.company.address.direccion')),
                    'departamento' => trim((string) config('einvoice.company.address.departamento')),
                    'provincia' => trim((string) config('einvoice.company.address.provincia')),
                    'distrito' => trim((string) config('einvoice.company.address.distrito')),
                    'ubigueo' => trim((string) config('einvoice.company.address.ubigueo')),
                    'codigoPais' => trim((string) config('einvoice.company.address.codigo_pais', 'PE')),
                    'codLocal' => trim((string) config('einvoice.company.address.cod_local', '0000')),
                ],
            ],
            'mtoOperGravadas' => $taxedBase,
            'mtoIGV' => $igv,
            'valorVenta' => $taxedBase,
            'totalImpuestos' => $igv,
            'subTotal' => round((float) $order->total_amount, 2),
            'mtoImpVenta' => round((float) $order->total_amount, 2),
            'details' => $details,
            'legends' => [[
                'code' => '1000',
                'value' => $this->amountService->toLegend((float) $order->total_amount, $currency),
            ]],
            'observacion' => 'Pedido '.($order->tracking_code ?: $order->id),
        ];
    }

    private function ensureCompanyCanInvoice(): void
    {
        $required = [
            'RUC de empresa' => config('einvoice.company.ruc'),
            'razon social de empresa' => config('einvoice.company.razon_social'),
            'direccion fiscal' => config('einvoice.company.address.direccion'),
            'departamento fiscal' => config('einvoice.company.address.departamento'),
            'provincia fiscal' => config('einvoice.company.address.provincia'),
            'distrito fiscal' => config('einvoice.company.address.distrito'),
            'ubigeo fiscal' => config('einvoice.company.address.ubigueo'),
        ];

        $missing = [];
        foreach ($required as $label => $value) {
            if (trim((string) $value) === '') {
                $missing[] = $label;
            }
        }

        if ($missing !== []) {
            throw new RuntimeException('Faltan datos fiscales para emitir comprobante: '.implode(', ', $missing).'.');
        }
    }
}
