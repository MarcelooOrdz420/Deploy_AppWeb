<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PromotionImageService
{
    public function resolve(?string $candidate, Product $product): string
    {
        foreach ([$candidate, $product->image_url] as $image) {
            $image = trim((string) $image);
            if ($image === '' || str_ends_with($image, '/images/products/default.svg')) {
                continue;
            }
            if (Str::startsWith($image, ['http://', 'https://'])
                || (Str::startsWith($image, '/storage/') && Storage::disk('public')->exists(Str::after($image, '/storage/')))
                || is_file(public_path(ltrim($image, '/')))) {
                return $image;
            }
        }

        $name = Str::lower(Str::ascii((string) $product->name));
        $knownImages = [
            '1/4 pollo' => '/images/products/pollos/cuarto.jpg',
            '1/4 de pollo' => '/images/products/pollos/cuarto.jpg',
            'cuarto de pollo' => '/images/products/pollos/cuarto.jpg',
            '1/2 pollo' => '/images/products/pollos/medio_pollo.jpg',
            'medio pollo' => '/images/products/pollos/medio_pollo.jpg',
            'pollo entero' => '/images/products/pollos/pollo_familiar.jpg',
            'mostrito' => '/images/products/pollos/mostrito.jpg',
            'mega combo' => '/images/products/pollos/mega-combo.jpg',
            'parrilla mixta' => '/images/products/parrillas/parrillada-mixta.jpg',
            'anticucho' => '/images/products/parrillas/anticuchos.jpg',
            'churrasco' => '/images/products/parrillas/parrilla_arge.jpg',
            'alitas' => '/images/products/parrillas/alitas-bbq.jpg',
            'brocheta' => '/images/products/parrillas/pollo_parrilla.jpg',
            'inca kola' => '/images/products/bebidas/inca-kola.jpg',
            'coca-cola' => '/images/products/bebidas/coca-cola.jpg',
            'sprite' => '/images/products/bebidas/sprite.jpg',
            'chicha' => '/images/products/bebidas/chicha_1L.jpg',
            'limonada' => '/images/products/bebidas/limonada.jpg',
            'agua' => '/images/products/bebidas/agua.jpg',
        ];

        foreach ($knownImages as $needle => $image) {
            if (Str::contains($name, $needle) && is_file(public_path(ltrim($image, '/')))) {
                return $image;
            }
        }

        return '/images/products/default.svg';
    }
}
