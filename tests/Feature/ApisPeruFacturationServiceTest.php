<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\ApisPeruFacturationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApisPeruFacturationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_beta_environment_simulates_invoice_without_external_request(): void
    {
        config([
            'einvoice.environment' => 'beta',
            'einvoice.fake_send' => true,
            'einvoice.company.ruc' => '20123456789',
            'einvoice.company.razon_social' => 'Pollos y Parrillas El Dorado S.A.C.',
            'einvoice.company.nombre_comercial' => 'Pollos y Parrillas El Dorado',
            'einvoice.company.address.direccion' => 'Av. Demo 123',
            'einvoice.company.address.departamento' => 'LIMA',
            'einvoice.company.address.provincia' => 'LIMA',
            'einvoice.company.address.distrito' => 'LIMA',
            'einvoice.company.address.ubigueo' => '150101',
        ]);

        Http::fake();

        $order = Order::query()->create([
            'tracking_code' => 'ED-DEMO01',
            'customer_name' => 'Cliente Demo',
            'customer_phone' => '999888777',
            'customer_email' => 'cliente@example.com',
            'delivery_type' => 'pickup',
            'status' => Order::STATUS_PENDING,
            'total_amount' => 59.00,
            'payment_method' => 'izipay',
            'payment_status' => 'verified',
            'billing_document_type' => 'dni',
            'billing_document_number' => '12345678',
            'billing_name' => 'Cliente Demo',
            'billing_receipt_type' => 'boleta',
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_name' => 'Pollo a la brasa',
            'unit_price' => 59.00,
            'quantity' => 1,
            'line_total' => 59.00,
        ]);

        $response = app(ApisPeruFacturationService::class)->sendInvoice($order);

        Http::assertNothingSent();
        $this->assertTrue($response['success']);
        $this->assertTrue($response['simulated']);
        $this->assertSame('B001-'.$order->id, $response['document']['numero']);
        $this->assertTrue($order->refresh()->billing_metadata['einvoice']['simulated']);
    }
}
