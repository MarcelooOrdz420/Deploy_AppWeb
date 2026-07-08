<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class NubefactService
{
    public function __construct(
        private readonly SpanishAmountService $amountService,
    ) {
    }

    public function sendInvoice(Order $order): array
    {
        $existing = data_get($order->billing_metadata, 'einvoice.response');
        if (is_array($existing) && ! empty(data_get($order->billing_metadata, 'einvoice.sent_at'))) {
            return [
                'ok' => true,
                'already_sent' => true,
                'response' => $existing,
            ];
        }

        $payload = $this->buildPayload($order);

        if ((bool) config('einvoice.fake_send', false)) {
            $data = [
                'ok' => true,
                'fake' => true,
                'tipo_de_comprobante' => $payload['tipo_de_comprobante'],
                'serie' => $payload['serie'],
                'numero' => $payload['numero'],
                'aceptada_por_sunat' => false,
                'sunat_description' => 'Envio simulado por EINVOICE_FAKE_SEND=true.',
            ];

            $metadata = $order->billing_metadata ?? [];
            $metadata['einvoice'] = [
                'provider' => 'nubefact',
                'payload' => $payload,
                'response' => $data,
                'sent_at' => now()->toIso8601String(),
                'fake' => true,
            ];

            $order->update([
                'billing_metadata' => $metadata,
            ]);

            return $data;
        }

        $response = Http::timeout((int) config('services.nubefact.timeout', 30))
            ->withHeaders([
                'Authorization' => $this->token(),
                'Content-Type' => 'application/json',
            ])
            ->acceptJson()
            ->post($this->route(), $payload);

        $data = $response->json();

        if ($response->failed() || ! is_array($data)) {
            throw new RuntimeException('No se pudo emitir el comprobante con Nubefact.');
        }

        if (isset($data['errors'])) {
            throw new RuntimeException('Nubefact rechazo el comprobante: '.(string) $data['errors']);
        }

        $metadata = $order->billing_metadata ?? [];
        $metadata['einvoice'] = [
            'provider' => 'nubefact',
            'payload' => $payload,
            'response' => $data,
            'sent_at' => now()->toIso8601String(),
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

    private function buildPayload(Order $order): array
    {
        $receiptType = (string) $order->billing_receipt_type;
        if (! in_array($receiptType, ['boleta', 'factura'], true)) {
            throw new RuntimeException('El pedido no tiene un tipo de comprobante valido.');
        }

        $this->ensureCanInvoice();

        $order->loadMissing('items');

        $series = $receiptType === 'factura'
            ? (string) config('einvoice.factura_series', 'F001')
            : (string) config('einvoice.boleta_series', 'B001');

        $documentType = $receiptType === 'factura' ? 1 : 2;
        $clientDocumentType = $order->billing_document_type === 'ruc' ? 6 : 1;
        $currencyCode = (string) config('einvoice.currency', 'PEN') === 'USD' ? 2 : 1;
        $total = round((float) $order->total_amount, 2);
        $taxedBase = round($total / 1.18, 2);
        $igv = round($total - $taxedBase, 2);

        return [
            'operacion' => 'generar_comprobante',
            'tipo_de_comprobante' => $documentType,
            'serie' => $series,
            'numero' => (int) $order->id,
            'sunat_transaction' => 1,
            'cliente_tipo_de_documento' => $clientDocumentType,
            'cliente_numero_de_documento' => (string) $order->billing_document_number,
            'cliente_denominacion' => (string) ($order->billing_name ?: $order->customer_name),
            'cliente_direccion' => (string) ($order->billing_address ?: $order->address ?: '-'),
            'cliente_email' => (string) ($order->billing_email ?: $order->customer_email ?: ''),
            'cliente_email_1' => '',
            'cliente_email_2' => '',
            'fecha_de_emision' => optional($order->created_at ?: now())->setTimezone('America/Lima')->format('d-m-Y'),
            'fecha_de_vencimiento' => '',
            'moneda' => $currencyCode,
            'tipo_de_cambio' => '',
            'porcentaje_de_igv' => 18.00,
            'descuento_global' => '',
            'total_descuento' => '',
            'total_anticipo' => '',
            'total_gravada' => $taxedBase,
            'total_inafecta' => '',
            'total_exonerada' => '',
            'total_igv' => $igv,
            'total_gratuita' => '',
            'total_otros_cargos' => '',
            'total' => $total,
            'percepcion_tipo' => '',
            'percepcion_base_imponible' => '',
            'total_percepcion' => '',
            'total_incluido_percepcion' => '',
            'retencion_tipo' => '',
            'retencion_base_imponible' => '',
            'total_retencion' => '',
            'total_impuestos_bolsas' => '',
            'detraccion' => false,
            'observaciones' => 'Pedido '.($order->tracking_code ?: $order->id).' | '.$this->amountService->toLegend($total, (string) config('einvoice.currency', 'PEN')),
            'documento_que_se_modifica_tipo' => '',
            'documento_que_se_modifica_serie' => '',
            'documento_que_se_modifica_numero' => '',
            'tipo_de_nota_de_credito' => '',
            'tipo_de_nota_de_debito' => '',
            'enviar_automaticamente_a_la_sunat' => (bool) config('services.nubefact.send_to_sunat', true),
            'enviar_automaticamente_al_cliente' => (bool) config('services.nubefact.send_to_customer', false),
            'condiciones_de_pago' => '',
            'medio_de_pago' => $this->paymentMethodLabel((string) $order->payment_method),
            'cancelado' => true,
            'placa_vehiculo' => '',
            'orden_compra_servicio' => '',
            'formato_de_pdf' => (string) config('services.nubefact.pdf_format', ''),
            'generado_por_contingencia' => '',
            'bienes_region_selva' => '',
            'servicios_region_selva' => '',
            'items' => $this->items($order),
        ];
    }

    private function items(Order $order): array
    {
        return $order->items->values()->map(function ($item, int $index): array {
            $lineTotal = round((float) $item->line_total, 2);
            $lineBase = round($lineTotal / 1.18, 2);
            $lineIgv = round($lineTotal - $lineBase, 2);
            $quantity = max((int) $item->quantity, 1);

            return [
                'unidad_de_medida' => 'NIU',
                'codigo' => 'P'.str_pad((string) ($item->product_id ?: $index + 1), 3, '0', STR_PAD_LEFT),
                'codigo_producto_sunat' => (string) config('services.nubefact.default_sunat_product_code', '10000000'),
                'descripcion' => (string) $item->product_name,
                'cantidad' => $quantity,
                'valor_unitario' => round($lineBase / $quantity, 2),
                'precio_unitario' => round($lineTotal / $quantity, 2),
                'descuento' => '',
                'subtotal' => $lineBase,
                'tipo_de_igv' => 1,
                'igv' => $lineIgv,
                'total' => $lineTotal,
                'anticipo_regularizacion' => false,
                'anticipo_documento_serie' => '',
                'anticipo_documento_numero' => '',
            ];
        })->all();
    }

    private function route(): string
    {
        $route = trim((string) config('services.nubefact.route'));
        if ($route === '') {
            throw new RuntimeException('Configura NUBEFACT_ROUTE con la ruta API de Nubefact.');
        }

        return $route;
    }

    private function token(): string
    {
        $token = trim((string) config('services.nubefact.token'));
        if ($token === '') {
            throw new RuntimeException('Configura NUBEFACT_TOKEN con el token API de Nubefact.');
        }

        return $token;
    }

    private function ensureCanInvoice(): void
    {
        $missing = [];
        foreach ([
            'NUBEFACT_ROUTE' => config('services.nubefact.route'),
            'NUBEFACT_TOKEN' => config('services.nubefact.token'),
        ] as $label => $value) {
            if (trim((string) $value) === '') {
                $missing[] = $label;
            }
        }

        if ($missing !== []) {
            throw new RuntimeException('Faltan credenciales de Nubefact: '.implode(', ', $missing).'.');
        }
    }

    private function paymentMethodLabel(string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'izipay' => 'Tarjeta',
            'cod' => 'Efectivo',
            'yape', 'plin' => 'Transferencia',
            default => $paymentMethod,
        };
    }
}
