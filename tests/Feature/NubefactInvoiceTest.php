<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\ElectronicInvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NubefactInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_nubefact_payload_can_be_previewed(): void
    {
        config([
            'einvoice.provider' => 'nubefact',
            'services.nubefact.route' => 'https://api.nubefact.test/api/v1/demo',
            'services.nubefact.token' => 'nubefact-token',
            'einvoice.boleta_series' => 'B001',
        ]);

        $order = $this->orderWithItem('boleta', 'dni', '12345678', 23.60);

        $payload = app(ElectronicInvoiceService::class)->previewPayload($order);

        $this->assertSame('generar_comprobante', $payload['operacion']);
        $this->assertSame(2, $payload['tipo_de_comprobante']);
        $this->assertSame('B001', $payload['serie']);
        $this->assertSame(20.0, $payload['total_gravada']);
        $this->assertSame(3.6, $payload['total_igv']);
        $this->assertSame(23.6, $payload['total']);
        $this->assertSame(1, $payload['items'][0]['tipo_de_igv']);
    }

    public function test_nubefact_send_uses_authorization_header_and_stores_metadata(): void
    {
        config([
            'einvoice.provider' => 'nubefact',
            'services.nubefact.route' => 'https://api.nubefact.test/api/v1/demo',
            'services.nubefact.token' => 'nubefact-token',
            'einvoice.factura_series' => 'F001',
        ]);

        Http::fake([
            'api.nubefact.test/*' => Http::response([
                'tipo_de_comprobante' => 1,
                'serie' => 'F001',
                'numero' => 1,
                'aceptada_por_sunat' => true,
                'enlace_del_pdf' => 'https://nubefact.test/demo.pdf',
            ]),
        ]);

        $order = $this->orderWithItem('factura', 'ruc', '20600695771', 118.00);

        $response = app(ElectronicInvoiceService::class)->sendInvoice($order);

        $this->assertTrue($response['aceptada_por_sunat']);
        $this->assertSame('nubefact', $order->fresh()->billing_metadata['einvoice']['provider']);

        Http::assertSent(fn ($request): bool => $request->hasHeader('Authorization', 'nubefact-token')
            && $request['operacion'] === 'generar_comprobante'
            && $request['tipo_de_comprobante'] === 1);
    }

    private function orderWithItem(string $receiptType, string $documentType, string $documentNumber, float $total): Order
    {
        $order = Order::query()->create([
            'tracking_code' => 'ED-NUBE01',
            'customer_name' => 'Cliente Nubefact',
            'customer_phone' => '999888777',
            'customer_email' => 'cliente@example.com',
            'delivery_type' => 'pickup',
            'status' => Order::STATUS_DELIVERED,
            'total_amount' => $total,
            'payment_method' => 'izipay',
            'payment_gateway' => 'izipay',
            'payment_status' => 'verified',
            'payment_verified_at' => now(),
            'billing_document_type' => $documentType,
            'billing_document_number' => $documentNumber,
            'billing_name' => 'Cliente Nubefact SAC',
            'billing_email' => 'cliente@example.com',
            'billing_address' => 'Av. Demo 123',
            'billing_receipt_type' => $receiptType,
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => 1,
            'product_name' => 'Producto de prueba',
            'unit_price' => $total,
            'quantity' => 1,
            'line_total' => $total,
        ]);

        return $order->fresh('items');
    }
}
