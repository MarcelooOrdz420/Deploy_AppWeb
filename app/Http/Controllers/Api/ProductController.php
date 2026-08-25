<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MarketingOffer;
use App\Models\Product;
use App\Services\InventoryMovementService;
use App\Services\PromotionImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(PromotionImageService $imageService): JsonResponse
    {
        $products = Product::query()
            ->where('is_available', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $offers = $this->activeOffersByProductId();

        return response()->json($products->map(
            fn (Product $product): array => $this->publicProductPayload($product, $offers->get($product->id), $imageService)
        )->values());
    }

    public function adminIndex(PromotionImageService $imageService): JsonResponse
    {
        $products = Product::query()
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $offers = $this->activeOffersByProductId();

        return response()->json($products->map(function (Product $product) use ($offers): array {
            $offer = $offers->get($product->id);
            $data = $product->toArray();
            $data['promotion_id'] = $offer?->id;
            $data['promo_price'] = $offer ? (float) $offer->promo_price : null;
            $data['discount_percent'] = $offer ? (float) $offer->discount_percent : null;
            $data['promotion_online_only'] = $offer ? (bool) $offer->online_only : null;

            return $data;
        })->values());
    }

    public function show(Product $product, PromotionImageService $imageService): JsonResponse
    {
        $offer = $this->activeOffersByProductId($product->id)->get($product->id);

        return response()->json($this->publicProductPayload($product, $offer, $imageService));
    }

    /**
     * Promociones vigentes ahora mismo (respeta is_active y la ventana de
     * horario), indexadas por product_id, para que el catalogo/detalle
     * publico siempre muestre el precio con descuento donde sea que el
     * producto aparezca, sin depender de la caja destacada de promociones.
     */
    private function activeOffersByProductId(?int $onlyProductId = null): \Illuminate\Support\Collection
    {
        $query = MarketingOffer::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderByRaw('ends_at IS NULL, ends_at ASC');

        if ($onlyProductId !== null) {
            $query->where('product_id', $onlyProductId);
        }

        return $query->get()->unique('product_id')->keyBy('product_id');
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'category' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'file', 'image', 'max:5120'],
            'is_available' => ['sometimes', 'boolean'],
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $data['image_url'] = $this->resolveImagePath($request, $data['image_url'] ?? null);

        $product = DB::transaction(function () use ($data, $request): Product {
            $product = Product::create($data);

            app(InventoryMovementService::class)->logProductOpening(
                product: $product,
                actor: $request->user(),
                note: 'Stock inicial registrado desde panel',
            );

            return $product;
        });

        return response()->json($product, 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'category' => ['sometimes', 'string', 'max:60'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'file', 'image', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'is_available' => ['sometimes', 'boolean'],
            'stock' => ['sometimes', 'integer', 'min:0'],
        ]);

        if ($request->boolean('remove_image')) {
            $data['image_url'] = null;
        } elseif ($request->hasFile('image')) {
            $data['image_url'] = $this->resolveImagePath($request, $product->image_url);
        } else {
            unset($data['image_url']);
        }

        $product = DB::transaction(function () use ($data, $product, $request): Product {
            $previousStock = (int) $product->stock;
            $product->update($data);
            $product->refresh();

            if (array_key_exists('stock', $data)) {
                app(InventoryMovementService::class)->logManualStockAdjustment(
                    product: $product,
                    previousStock: $previousStock,
                    newStock: (int) $product->stock,
                    actor: $request->user(),
                    note: 'Ajuste manual desde administracion',
                );
            }

            return $product;
        });

        return response()->json($product);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Producto eliminado']);
    }

    private function resolveImagePath(Request $request, ?string $fallback = null): ?string
    {
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products/admin', 'public');

            return Storage::url($path);
        }

        return $fallback;
    }

    private function publicProductPayload(Product $product, ?MarketingOffer $offer = null, ?PromotionImageService $imageService = null): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'category' => $product->category,
            'description' => $product->description,
            'price' => $product->price,
            'image_url' => $product->image_url,
            'is_available' => (bool) $product->is_available,
            'is_sold_out' => $product->is_sold_out,
            'can_sell' => $product->can_sell,
            'availability_label' => $product->availability_label,
            'promotion_id' => $offer?->id,
            'promo_price' => $offer ? (float) $offer->promo_price : null,
            'discount_percent' => $offer ? (float) $offer->discount_percent : null,
            'promotion_online_only' => $offer ? (bool) $offer->online_only : null,
            'promotion_ends_at' => $offer?->ends_at?->toIso8601String(),
            'promotion_image_url' => $offer && $imageService ? $imageService->resolve($offer->image_url, $product) : null,
        ];
    }
}
