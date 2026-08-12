<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\PromotionImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PromotionImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_quarter_chicken_image_uses_existing_catalog_asset(): void
    {
        $product = Product::query()->create([
            'name' => '1/4 de pollo a la brasa',
            'category' => 'pollos',
            'description' => 'Promocion de prueba',
            'price' => 18.90,
            'image_url' => '/images/products/pollos/archivo-inexistente.jpg',
            'is_available' => true,
            'stock' => 10,
        ]);

        $resolved = app(PromotionImageService::class)->resolve(null, $product);

        $this->assertSame('/images/products/pollos/cuarto.jpg', $resolved);
        $this->assertFileExists(public_path(ltrim($resolved, '/')));
    }

    public function test_uploaded_image_is_returned_as_a_same_origin_path(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('offers/admin/promo.jpg', 'image-content');
        $product = $this->product('/images/products/default.svg');

        $resolved = app(PromotionImageService::class)->resolve(
            'http://dominio-antiguo.test/storage/offers/admin/promo.jpg',
            $product,
        );

        $this->assertSame('/media/promotions/offers/admin/promo.jpg', $resolved);
    }

    public function test_promotion_image_is_served_without_public_storage_symlink(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('offers/admin/promo.jpg', 'image-content');

        $this->get('/media/promotions/offers/admin/promo.jpg')
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=86400, public')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_insecure_or_unrelated_remote_image_is_rejected(): void
    {
        config(['app.url' => 'https://pollos.example.com']);
        $product = $this->product('/images/products/default.svg');

        $this->assertSame(
            '/images/products/default.svg',
            app(PromotionImageService::class)->resolve('http://pollos.example.com/promo.jpg', $product),
        );
        $this->assertSame(
            '/images/products/default.svg',
            app(PromotionImageService::class)->resolve('https://otro-dominio.example/promo.jpg', $product),
        );
    }

    public function test_storage_path_traversal_is_rejected(): void
    {
        Storage::fake('public');
        $product = $this->product('/images/products/default.svg');

        $this->assertSame(
            '/images/products/default.svg',
            app(PromotionImageService::class)->resolve('/storage/%2e%2e/private/secret.jpg', $product),
        );
    }

    private function product(string $imageUrl): Product
    {
        return new Product([
            'name' => 'Producto de prueba sin imagen conocida',
            'category' => 'general',
            'description' => 'Promocion de prueba',
            'price' => 18.90,
            'image_url' => $imageUrl,
            'is_available' => true,
            'stock' => 10,
        ]);
    }
}
