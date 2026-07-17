<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Services\Payments\IzipayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
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
        $this->call('HEAD', '/pagos/izipay/ipn')->assertOk();
        $this->post('/pagos/izipay/ipn')->assertOk()->assertSee('OK');
        $this->post('/pagos/izipay/ipn', ['kr-answer' => '{}'])->assertUnauthorized();
    }

    public function test_invalid_hmac_and_invalid_json_are_rejected(): void
    {
        $this->post('/pagos/izipay/ipn', [
            'kr-answer' => '{}', 'kr-hash' => 'invalid', 'kr-hash-algorithm' => 'HMAC-SHA-256',
        ])->assertUnauthorized();

        $answer = '{invalid';
        $this->post('/pagos/izipay/ipn', [
            'kr-answer' => $answer,
            'kr-hash' => hash_hmac('sha256', $answer, 'hmac-secret'),
            'kr-hash-algorithm' => 'HMAC-SHA-256',
        ])->assertBadRequest();
    }

    public function test_relay_secret_is_required_when_enabled(): void
    {
        config(['services.izipay.require_relay' => true, 'services.izipay.relay_secret' => 'relay-secret']);

        $this->withHeader('X-Relay-Secret', 'wrong')->post('/pagos/izipay/ipn')->assertUnauthorized();
        $this->withHeader('X-Relay-Secret', 'relay-secret')->post('/pagos/izipay/ipn')->assertOk();
    }

    public function test_signed_browser_return_confirms_payment_and_later_ipn_is_idempotent(): void
    {
        $order = $this->orderWithAttempt();
        $answer = json_encode([
            'shopId' => 'shop-id', 'orderStatus' => 'PAID',
            'orderDetails' => ['orderId' => $order->tracking_code, 'amount' => 2550, 'currency' => 'PEN'],
            'transactions' => [['uuid' => 'tx-browser-first', 'status' => 'PAID']],
        ], JSON_THROW_ON_ERROR);
        $payload = ['kr-answer' => $answer, 'kr-hash' => hash_hmac('sha256', $answer, 'hmac-secret'),
            'kr-hash-algorithm' => 'HMAC-SHA-256'];
        $returnUrl = URL::temporarySignedRoute('izipay.result', now()->addMinutes(5), ['order' => $order->id]);

        $this->post($returnUrl, $payload)->assertOk();
        $this->post('/pagos/izipay/ipn', $payload)->assertOk();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'verified']);
        $this->assertSame(1, PaymentTransaction::where('transaction_uuid', 'tx-browser-first')->count());
    }

    public function test_invalid_browser_return_signature_does_not_modify_order(): void
    {
        $order = $this->orderWithAttempt();
        $answer = json_encode([
            'shopId' => 'shop-id', 'orderStatus' => 'PAID',
            'orderDetails' => ['orderId' => $order->tracking_code, 'amount' => 2550, 'currency' => 'PEN'],
            'transactions' => [['uuid' => 'tx-invalid-browser', 'status' => 'PAID']],
        ], JSON_THROW_ON_ERROR);
        $returnUrl = URL::temporarySignedRoute('izipay.result', now()->addMinutes(5), ['order' => $order->id]);

        $this->post($returnUrl, ['kr-answer' => $answer, 'kr-hash' => 'invalid',
            'kr-hash-algorithm' => 'HMAC-SHA-256'])->assertOk();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'pending']);
    }

    public function test_ipn_first_and_browser_return_afterwards_do_not_duplicate_records(): void
    {
        $order = $this->orderWithAttempt();
        $answer = json_encode([
            'shopId' => 'shop-id', 'orderStatus' => 'PAID',
            'orderDetails' => ['orderId' => $order->tracking_code, 'amount' => 2550, 'currency' => 'PEN'],
            'transactions' => [['uuid' => 'tx-ipn-first', 'status' => 'PAID']],
        ], JSON_THROW_ON_ERROR);
        $payload = ['kr-answer' => $answer, 'kr-hash' => hash_hmac('sha256', $answer, 'hmac-secret'),
            'kr-hash-algorithm' => 'HMAC-SHA-256'];

        $this->post('/pagos/izipay/ipn', $payload)->assertOk();
        $this->post(URL::temporarySignedRoute('izipay.result', now()->addMinutes(5), ['order' => $order->id]), $payload)
            ->assertOk();

        $this->assertSame(1, PaymentTransaction::where('transaction_uuid', 'tx-ipn-first')->count());
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'verified']);
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
        $this->post('/pagos/izipay/ipn', $payload)->assertOk()->assertSeeText('OK');
        $this->post('/pagos/izipay/ipn', $payload)->assertOk()->assertSeeText('OK');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'verified', 'payment_reference' => 'tx-unique-1']);
        $this->assertSame(1, PaymentTransaction::where('transaction_uuid', 'tx-unique-1')->count());
        $this->assertDatabaseHas('payment_transactions', ['order_id' => $order->id, 'status' => 'verified']);
    }

    public function test_rejected_notification_updates_order_and_transaction(): void
    {
        $order = $this->orderWithAttempt();
        $this->sendNotification($order, 'REFUSED', 'tx-refused')->assertOk();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'rejected']);
        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id, 'transaction_uuid' => 'tx-refused', 'status' => 'rejected',
        ]);
    }

    public function test_verified_order_cannot_be_downgraded(): void
    {
        $order = $this->orderWithAttempt();
        $this->sendNotification($order, 'PAID', 'tx-stable')->assertOk();
        $this->sendNotification($order, 'REFUSED', 'tx-stable')->assertOk();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'verified']);
        $this->assertDatabaseHas('payment_transactions', ['transaction_uuid' => 'tx-stable', 'status' => 'verified']);
    }

    public function test_hash_can_be_received_in_izipay_header(): void
    {
        $order = $this->order();
        PaymentTransaction::create(['order_id' => $order->id, 'provider' => 'izipay', 'status' => 'pending',
            'amount' => 25.50, 'currency' => 'PEN', 'merchant_order_id' => $order->tracking_code]);
        $answer = json_encode(['shopId' => 'shop-id', 'orderStatus' => 'PAID',
            'orderDetails' => ['orderId' => $order->tracking_code, 'amount' => 2550, 'currency' => 'PEN'],
            'transactions' => [['uuid' => 'tx-header-1', 'status' => 'PAID']]], JSON_THROW_ON_ERROR);

        $this->withHeader('X-KR-HASH', hash_hmac('sha256', $answer, 'hmac-secret'))
            ->withHeader('X-KR-HASH-ALGORITHM', 'HMAC-SHA-256')
            ->post('/pagos/izipay/ipn', ['kr-answer' => $answer])
            ->assertOk()->assertSeeText('OK');
    }

    public function test_wrong_amount_does_not_mark_order_paid(): void
    {
        $order = $this->order();
        PaymentTransaction::create(['order_id' => $order->id, 'amount' => 25.50, 'currency' => 'PEN',
            'merchant_order_id' => $order->tracking_code]);
        $answer = json_encode(['shopId' => 'shop-id', 'orderStatus' => 'PAID',
            'orderDetails' => ['orderId' => $order->tracking_code, 'amount' => 1, 'currency' => 'PEN'],
            'transactions' => [['uuid' => 'tx-bad']]], JSON_THROW_ON_ERROR);
        $this->post('/pagos/izipay/ipn', ['kr-answer' => $answer,
            'kr-hash' => hash_hmac('sha256', $answer, 'hmac-secret'), 'kr-hash-algorithm' => 'HMAC-SHA-256'])
            ->assertBadRequest();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'pending']);
    }

    public function test_wrong_shop_id_does_not_mark_order_paid(): void
    {
        $order = $this->orderWithAttempt();
        $this->sendNotification($order, 'PAID', 'tx-shop', ['shopId' => 'another-shop'])
            ->assertBadRequest();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'pending']);
    }

    public function test_wrong_currency_does_not_mark_order_paid(): void
    {
        $order = $this->orderWithAttempt();
        $this->sendNotification($order, 'PAID', 'tx-currency', ['orderDetails.currency' => 'USD'])
            ->assertBadRequest();
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

    private function orderWithAttempt(): Order
    {
        $order = $this->order();
        PaymentTransaction::create([
            'order_id' => $order->id, 'provider' => 'izipay', 'status' => 'pending',
            'amount' => 25.50, 'currency' => 'PEN', 'merchant_order_id' => $order->tracking_code,
        ]);

        return $order;
    }

    private function sendNotification(Order $order, string $status, string $uuid, array $overrides = [])
    {
        $payload = [
            'shopId' => 'shop-id',
            'orderStatus' => $status,
            'orderDetails' => ['orderId' => $order->tracking_code, 'amount' => 2550, 'currency' => 'PEN'],
            'transactions' => [['uuid' => $uuid, 'status' => $status, 'detailedStatus' => $status]],
        ];
        foreach ($overrides as $key => $value) {
            data_set($payload, $key, $value);
        }
        $answer = json_encode($payload, JSON_THROW_ON_ERROR);

        return $this->post('/pagos/izipay/ipn', [
            'kr-answer' => $answer,
            'kr-hash' => hash_hmac('sha256', $answer, 'hmac-secret'),
            'kr-hash-algorithm' => 'HMAC-SHA-256',
        ]);
    }
}
