<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\ElectronicInvoiceService;
use App\Services\ElectronicReceiptDeliveryService;
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

    public function test_automatic_send_is_idempotent_after_verified_payment(): void
    {
        config([
            'einvoice.auto_send' => true,
            'einvoice.fake_send' => true,
            'einvoice.provider' => 'nubefact',
            'services.nubefact.route' => 'https://api.nubefact.test/api/v1/demo',
            'services.nubefact.token' => 'nubefact-token',
        ]);

        $order = $this->orderWithItem('boleta', 'dni', '12345678', 23.60);

        $first = app(ElectronicInvoiceService::class)->sendIfEligible($order);
        $second = app(ElectronicInvoiceService::class)->sendIfEligible($order->fresh('items'));

        $this->assertTrue($first['ok']);
        $this->assertTrue($second['already_sent']);
        $this->assertNotEmpty($order->fresh()->billing_metadata['einvoice']['sent_at']);
    }

    public function test_verified_payment_automatically_issues_and_requests_customer_delivery(): void
    {
        config([
            'einvoice.provider' => 'nubefact',
            'einvoice.auto_send' => true,
            'services.nubefact.route' => 'https://api.nubefact.test/api/v1/demo',
            'services.nubefact.token' => 'nubefact-token',
            'services.nubefact.send_to_customer' => true,
        ]);
        Http::fake(['api.nubefact.test/*' => Http::response([
            'aceptada_por_sunat' => true,
            'enlace_del_pdf' => 'https://nubefact.test/auto.pdf',
        ])]);

        $order = $this->orderWithItem('boleta', 'dni', '12345678', 23.60);
        $result = app(ElectronicReceiptDeliveryService::class)->issueAfterVerifiedPayment($order);

        $this->assertTrue($result['ok']);
        $this->assertSame('requested', data_get($order->fresh()->billing_metadata, 'einvoice.delivery.status'));
        Http::assertSent(fn ($request): bool => $request->url() === 'https://api.nubefact.test/api/v1/demo'
            && $request['enviar_automaticamente_al_cliente'] === true);
    }

    public function test_admin_retry_emails_a_copy_without_issuing_a_second_invoice(): void
    {
        config([
            'einvoice.provider' => 'nubefact',
            'services.nubefact.route' => 'https://api.nubefact.test/api/v1/demo',
            'services.nubefact.token' => 'nubefact-token',
            'services.resend.key' => 'resend-key',
            'services.resend.from_address' => 'ventas@example.com',
            'services.resend.from_name' => 'El Dorado',
        ]);
        Http::fake([
            'api.nubefact.test/*' => Http::response([
                'aceptada_por_sunat' => true,
                'enlace_del_pdf' => 'https://nubefact.test/manual.pdf',
            ]),
            'api.resend.com/*' => Http::response(['id' => 'email-1'], 200),
        ]);

        $order = $this->orderWithItem('factura', 'ruc', '20600695771', 118.00);
        app(ElectronicInvoiceService::class)->sendInvoice($order);
        $result = app(ElectronicReceiptDeliveryService::class)->sendCustomerCopy($order->fresh());

        $this->assertTrue($result['ok']);
        $this->assertSame('cliente@example.com', $result['recipient']);
        $this->assertSame('sent', data_get($order->fresh()->billing_metadata, 'einvoice.delivery.status'));
        Http::assertSentCount(2);
        Http::assertSent(fn ($request): bool => $request->url() === 'https://api.resend.com/emails'
            && $request['to'] === ['cliente@example.com']
            && ! empty($request['attachments'][0]['content']));
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
            'payment_reference' => 'IZIPAY-TEST-001',
            'billing_document_type' => $documentType,
            'billing_document_number' => $documentNumber,
            'billing_name' => 'Cliente Nubefact SAC',
            'billing_email' => 'cliente@example.com',
            'billing_address' => 'Av. Demo 123',
            'billing_receipt_type' => $receiptType,
        ]);

        $product = Product::query()->create([
            'name' => 'Producto de prueba',
            'category' => 'pollos',
            'description' => 'Producto para pruebas de comprobante.',
            'price' => $total,
            'is_available' => true,
            'stock' => 10,
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Producto de prueba',
            'unit_price' => $total,
            'quantity' => 1,
            'line_total' => $total,
        ]);

        return $order->fresh('items');
    }
}
