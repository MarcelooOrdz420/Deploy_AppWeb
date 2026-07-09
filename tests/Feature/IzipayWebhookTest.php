<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\Payments\IzipayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class IzipayWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_izipay_notification_url_accepts_validation_requests(): void
    {
        $this->get('/izipay-ipn')->assertOk()->assertSee('OK');
        $this->head('/izipay-ipn')->assertOk();
        $this->post('/izipay-ipn', ['validation' => 'ping'])
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'ignored' => true,
            ]);
    }

    public function test_izipay_payment_uses_configured_ipn_url(): void
    {
        config([
            'services.izipay.ipn_url' => 'https://pollos.saborcentral.com/izipay-ipn.php',
            'services.izipay.shop_id' => 'shop-id',
            'services.izipay.rest_api_key' => 'rest-key',
            'services.izipay.public_key' => 'public-key',
        ]);

        Http::fake([
            'api.micuentaweb.pe/*' => Http::response([
                'answer' => [
                    'formToken' => 'test-form-token',
                ],
            ]),
        ]);

        $order = Order::query()->create([
            'tracking_code' => 'ED-TEST01',
            'customer_name' => 'Cliente Test',
            'customer_phone' => '999888777',
            'delivery_type' => 'pickup',
            'status' => Order::STATUS_PENDING,
            'total_amount' => 25.50,
            'payment_method' => 'izipay',
            'payment_status' => 'pending',
        ]);

        app(IzipayService::class)->createPayment($order);

        Http::assertSent(fn ($request): bool => $request['ipnTargetUrl'] === 'https://pollos.saborcentral.com/izipay-ipn.php');
    }

    public function test_izipay_payment_uses_php_webhook_route_when_ipn_url_is_not_configured(): void
    {
        config([
            'app.url' => 'https://pollos.saborcentral.com',
            'services.izipay.ipn_url' => null,
            'services.izipay.shop_id' => 'shop-id',
            'services.izipay.rest_api_key' => 'rest-key',
            'services.izipay.public_key' => 'public-key',
        ]);

        Http::fake([
            'api.micuentaweb.pe/*' => Http::response([
                'answer' => [
                    'formToken' => 'test-form-token',
                ],
            ]),
        ]);

        $order = Order::query()->create([
            'tracking_code' => 'ED-TEST02',
            'customer_name' => 'Cliente Test',
            'customer_phone' => '999888777',
            'delivery_type' => 'pickup',
            'status' => Order::STATUS_PENDING,
            'total_amount' => 25.50,
            'payment_method' => 'izipay',
            'payment_status' => 'pending',
        ]);

        app(IzipayService::class)->createPayment($order);

        Http::assertSent(fn ($request): bool => $request['ipnTargetUrl'] === 'https://pollos.saborcentral.com/izipay-ipn.php');
    }

    public function test_izipay_payment_requires_public_https_ipn_url(): void
    {
        config([
            'services.izipay.ipn_url' => 'http://localhost/izipay-ipn.php',
            'services.izipay.shop_id' => 'shop-id',
            'services.izipay.rest_api_key' => 'rest-key',
            'services.izipay.public_key' => 'public-key',
        ]);

        $order = Order::query()->create([
            'tracking_code' => 'ED-TEST03',
            'customer_name' => 'Cliente Test',
            'customer_phone' => '999888777',
            'delivery_type' => 'pickup',
            'status' => Order::STATUS_PENDING,
            'total_amount' => 25.50,
            'payment_method' => 'izipay',
            'payment_status' => 'pending',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('La URL de notificacion de Izipay debe usar HTTPS.');

        app(IzipayService::class)->createPayment($order);
    }
}
