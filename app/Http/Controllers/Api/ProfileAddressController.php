<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileAddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = UserAddress::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($addresses);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:80'],
            'address' => ['required', 'string', 'max:255'],
        ]);

        $address = UserAddress::create([
            'user_id' => $request->user()->id,
            'label' => $data['label'] ?? null,
            'address' => $data['address'],
        ]);

        return response()->json($address, 201);
    }

    public function destroy(Request $request, UserAddress $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $address->delete();

        return response()->json(['message' => 'Direccion eliminada']);
    }
}
