<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_repeated_checkout_key_returns_the_existing_cod_order(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_verified' => true,
            'role' => 'customer',
        ]);
        $product = Product::create([
            'name' => 'Producto de prueba',
            'category' => 'bebidas',
            'description' => 'Prueba',
            'price' => 10,
            'is_available' => true,
            'stock' => 10,
        ]);
        $payload = [
            'idempotency_key' => 'checkout-test-123',
            'customer_name' => 'Cliente Prueba',
            'customer_phone' => '999999999',
            'delivery_type' => 'pickup',
            'payment_method' => 'cod',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ];
        $headers = ['Authorization' => 'Bearer '.JwtService::encode($user)];

        $first = $this->postJson('/api/v1/orders', $payload, $headers)
            ->assertCreated()
            ->assertJsonPath('payment_method', 'cod');

        $this->postJson('/api/v1/orders', $payload, $headers)
            ->assertOk()
            ->assertJsonPath('id', $first->json('id'))
            ->assertJsonPath('idempotent_replay', true);

        $this->assertSame(1, Order::count());
        $this->assertSame(9, $product->fresh()->stock);
    }
}
