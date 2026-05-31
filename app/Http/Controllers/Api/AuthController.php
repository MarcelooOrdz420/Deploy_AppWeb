<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LoginHistory;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role' => 'customer',
            'password' => Hash::make($data['password']),
        ]);

        $token = JwtService::encode($user);

        return response()->json([
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        // record attempt (successful flag will be updated below)
        $historyData = [
            'email' => $data['email'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'successful' => false,
        ];

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            // persist failure (no user_id when not found)
            LoginHistory::create($historyData);
            throw ValidationException::withMessages([
                'email' => ['Credenciales incorrectas.'],
            ]);
        }

        if (! $user->is_active) {
            LoginHistory::create(array_merge($historyData, ['user_id' => $user->id]));
            throw ValidationException::withMessages([
                'email' => ['Cuenta desactivada. Contacta con soporte de Pollos y Parrillas El Dorado.'],
            ]);
        }

        // success
        LoginHistory::create(array_merge($historyData, ['user_id' => $user->id, 'successful' => true]));

        return response()->json([
            'token' => JwtService::encode($user),
            'user' => $user,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}
