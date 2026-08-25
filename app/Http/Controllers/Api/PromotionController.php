<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MarketingOffer;
use App\Services\PromotionImageService;
use Illuminate\Http\JsonResponse;

class PromotionController extends Controller
{
    /**
     * Maximo de promociones que se muestran a la vez en la caja destacada
     * del inicio (web y movil). Si hay mas activas que esto, las de mas
     * (las mas nuevas) igual se pueden enviar por push/correo, solo no
     * entran al carrusel del banner.
     */
    public const BANNER_LIMIT = 3;

    /**
     * Promociones vigentes ahora mismo (respetan is_active y la ventana de
     * horario), para la caja destacada del inicio en web y movil. Se
     * muestran hasta BANNER_LIMIT, ordenadas por la que termina mas pronto.
     * 'offer' se mantiene por compatibilidad y siempre es la primera de
     * 'offers'.
     */
    public function active(PromotionImageService $imageService): JsonResponse
    {
        $offers = MarketingOffer::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->with('product')
            ->orderByRaw('ends_at IS NULL, ends_at ASC')
            ->limit(self::BANNER_LIMIT)
            ->get()
            ->filter(fn (MarketingOffer $offer) => (bool) $offer->product)
            ->values();

        if ($offers->isEmpty()) {
            return response()->json(['active' => false]);
        }

        $payload = $offers->map(function (MarketingOffer $offer) use ($imageService): array {
            $product = $offer->product;

            return [
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
            ];
        })->values();

        return response()->json([
            'active' => true,
            'offer' => $payload->first(),
            'offers' => $payload,
        ]);
    }
}
