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
            $resolved = $this->resolveCandidate((string) $image);
            if ($resolved !== null) {
                return $resolved;
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

    private function resolveCandidate(string $image): ?string
    {
        $image = trim($image);
        if ($image === '' || str_ends_with($image, '/images/products/default.svg')) {
            return null;
        }

        $scheme = strtolower((string) parse_url($image, PHP_URL_SCHEME));
        $host = strtolower((string) parse_url($image, PHP_URL_HOST));
        $path = parse_url($image, PHP_URL_PATH);
        if (! is_string($path) || $path === '') {
            return null;
        }

        $normalizedPath = '/'.ltrim($path, '/');

        // Uploaded files are always returned as same-origin paths. This also
        // repairs records created with an old APP_URL or an insecure http URL.
        if (Str::startsWith($normalizedPath, ['/storage/', '/media/promotions/'])) {
            $prefix = Str::startsWith($normalizedPath, '/storage/')
                ? '/storage/'
                : '/media/promotions/';
            $relativePath = rawurldecode(Str::after($normalizedPath, $prefix));
            if ($relativePath === '' || Str::contains($relativePath, ['..', '\\'])) {
                return null;
            }

            return Storage::disk('public')->exists($relativePath)
                ? '/media/promotions/'.$relativePath
                : null;
        }

        // Public application assets are valid only when they exist locally.
        if ($scheme === '' && $host === '' && is_file(public_path(ltrim($normalizedPath, '/')))) {
            return $normalizedPath;
        }

        // Never render remote HTTP content or assets from an unrelated host.
        if ($scheme !== 'https' || $host === '') {
            return null;
        }

        $appHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
        if ($appHost === '' || ! hash_equals($appHost, $host)) {
            return null;
        }

        return is_file(public_path(ltrim($normalizedPath, '/')))
            ? $normalizedPath
            : null;
    }
}
