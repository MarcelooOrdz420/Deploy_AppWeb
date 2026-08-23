<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MarketingOffer;
use App\Services\PromotionImageService;
use Illuminate\Http\JsonResponse;

class PromotionController extends Controller
{
    /**
     * Promocion vigente ahora mismo (respeta is_active y la ventana de
     * horario), para la caja destacada del inicio en web y movil. Si hay
     * varias activas a la vez, se muestra la que termina mas pronto.
     */
    public function active(PromotionImageService $imageService): JsonResponse
    {
        $offer = MarketingOffer::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->with('product')
            ->orderByRaw('ends_at IS NULL, ends_at ASC')
            ->first();

        if (! $offer || ! $offer->product) {
            return response()->json(['active' => false]);
        }

        $product = $offer->product;

        return response()->json([
            'active' => true,
            'offer' => [
                'id' => $offer->id,
                'title' => $offer->title,
                'message' => $offer->message,
                'body' => $offer->body,
                'image_url' => $imageService->resolve($offer->image_url, $product),
                'original_price' => (float) $offer->original_price,
                'promo_price' => (float) $offer->promo_price,
                'discount_percent' => (float) $offer->discount_percent,
                'starts_at' => $offer->starts_at?->toIso8601String(),
                'ends_at' => $offer->ends_at?->toIso8601String(),
                'url' => '/promociones/'.$offer->id,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'description' => $product->description,
                    'category' => $product->category,
                    'image_url' => $product->image_url,
                    'stock' => (int) ($product->stock ?? 0),
                ],
            ],
        ]);
    }
}
