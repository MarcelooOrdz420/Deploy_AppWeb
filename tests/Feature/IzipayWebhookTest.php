<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Services\Payments\IzipayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IzipayWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.izipay.enabled' => true, 'services.izipay.shop_id' => 'shop-id', 'services.izipay.rest_api_key' => 'rest-key',
            'services.izipay.public_key' => 'public-key', 'services.izipay.hmac_key' => 'hmac-secret',
            'services.izipay.ipn_url' => 'https://pollos.example/pagos/izipay/ipn']);
    }

    public function test_health_check_is_available_but_unsigned_post_cannot_confirm_payment(): void
    {
        $this->get('/pagos/izipay/ipn')->assertOk()->assertSee('OK');
        $this->post('/pagos/izipay/ipn')->assertOk()->assertSee('OK');
        $this->post('/pagos/izipay/ipn', ['kr-answer' => '{}'])->assertUnauthorized();
    }

    public function test_form_token_is_created_from_backend_amount_and_stored_encrypted(): void
    {
        Http::fake(['api.micuentaweb.pe/*' => Http::response(['answer' => ['formToken' => 'secret-token']])]);
        $order = $this->order();
        $result = app(IzipayService::class)->createPayment($order);
        $this->assertSame('ED-TEST01', $result['orderId']);
        $this->assertStringNotContainsString('form_token=', $result['payment_url']);
        Http::assertSent(fn ($request): bool => $request['amount'] === 2550 && $request['currency'] === 'PEN');
        $this->assertDatabaseHas('payment_transactions', ['order_id' => $order->id, 'amount' => 25.50, 'status' => 'pending']);
    }

    public function test_valid_approved_notification_is_idempotent(): void
    {
        $order = $this->order();
        PaymentTransaction::create(['order_id' => $order->id, 'provider' => 'izipay', 'status' => 'pending',
            'amount' => 25.50, 'currency' => 'PEN', 'merchant_order_id' => $order->tracking_code]);
        $answer = json_encode(['shopId' => 'shop-id', 'orderStatus' => 'PAID',
            'orderDetails' => ['orderId' => $order->tracking_code, 'amount' => 2550, 'currency' => 'PEN'],
            'transactions' => [['uuid' => 'tx-unique-1', 'status' => 'PAID', 'detailedStatus' => 'AUTHORISED']]], JSON_THROW_ON_ERROR);
        $hash = hash_hmac('sha256', $answer, 'hmac-secret');
        $payload = ['kr-answer' => $answer, 'kr-hash' => $hash, 'kr-hash-algorithm' => 'HMAC-SHA-256'];
        $this->post('/pagos/izipay/ipn', $payload)->assertOk()->assertJson(['status' => 'verified', 'duplicate' => false]);
        $this->post('/pagos/izipay/ipn', $payload)->assertOk()->assertJson(['status' => 'verified', 'duplicate' => true]);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'verified', 'payment_reference' => 'tx-unique-1']);
    }

    public function test_wrong_amount_or_currency_does_not_mark_order_paid(): void
    {
        $order = $this->order();
        PaymentTransaction::create(['order_id' => $order->id, 'amount' => 25.50, 'currency' => 'PEN',
            'merchant_order_id' => $order->tracking_code]);
        $answer = json_encode(['shopId' => 'shop-id', 'orderStatus' => 'PAID',
            'orderDetails' => ['orderId' => $order->tracking_code, 'amount' => 1, 'currency' => 'USD'],
            'transactions' => [['uuid' => 'tx-bad']]], JSON_THROW_ON_ERROR);
        $this->post('/pagos/izipay/ipn', ['kr-answer' => $answer,
            'kr-hash' => hash_hmac('sha256', $answer, 'hmac-secret'), 'kr-hash-algorithm' => 'HMAC-SHA-256'])
            ->assertStatus(422);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'pending']);
    }

    public function test_orders_view_uses_stored_status_messages(): void
    {
        $this->get('/mis-pedidos')->assertOk()->assertSee('Pago realizado exitosamente')->assertSee('Pago pendiente de confirmacion');
    }

    private function order(): Order
    {
        $order = Order::create(['tracking_code' => 'ED-TEST01', 'customer_name' => 'Cliente Test',
            'customer_phone' => '999888777', 'delivery_type' => 'pickup', 'status' => Order::STATUS_PENDING,
            'total_amount' => 25.50, 'payment_method' => 'izipay', 'payment_gateway' => 'izipay', 'payment_status' => 'pending']);
        OrderItem::create(['order_id' => $order->id, 'product_name' => 'Pollo', 'unit_price' => 25.50,
            'quantity' => 1, 'line_total' => 25.50]);
        return $order;
    }
}
