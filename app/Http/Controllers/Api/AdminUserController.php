<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::query()
            ->orderByDesc('created_at')
            ->paginate(30);

        return response()->json($users);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        if ($request->user()->id === $user->id && $request->has('is_active') && ! $request->boolean('is_active')) {
            return response()->json(['message' => 'No puedes desactivar tu propia cuenta admin.'], 422);
        }

        $data = $request->validate([
            'is_active' => ['sometimes', 'boolean'],
            'role' => ['sometimes', 'string', 'in:admin,customer'],
        ]);

        $user->update($data);

        return response()->json($user->fresh());
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'No puedes eliminar tu propia cuenta admin.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'Cuenta eliminada']);
    }
}
