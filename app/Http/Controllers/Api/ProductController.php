<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\InventoryMovementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::query()
            ->where('is_available', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return response()->json($products);
    }

    public function adminIndex(): JsonResponse
    {
        $products = Product::query()
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return response()->json($products);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json($product);
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
}
