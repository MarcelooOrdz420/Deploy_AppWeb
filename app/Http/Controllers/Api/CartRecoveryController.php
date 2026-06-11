<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CartRecoveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartRecoveryController extends Controller
{
    public function sync(Request $request, CartRecoveryService $cartRecoveryService): JsonResponse
    {
        $data = $request->validate([
            'source' => ['nullable', 'string', 'max:30'],
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer'],
            'items.*.name' => ['required', 'string', 'max:160'],
            'items.*.category' => ['nullable', 'string', 'max:80'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.qty' => ['required', 'integer', 'min:0'],
            'items.*.image_url' => ['nullable', 'string', 'max:500'],
        ]);

        $cartRecoveryService->syncForUser(
            user: $request->user(),
            items: $data['items'],
            source: $data['source'] ?? 'web',
        );

        return response()->json([
            'message' => 'Carrito sincronizado correctamente.',
        ]);
    }
}
