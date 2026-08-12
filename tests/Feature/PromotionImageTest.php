<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\PromotionImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
