<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LoginHistory;
use App\Services\Auth\GoogleIdentityService;
use App\Services\Auth\OtpService;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request, OtpService $otpService): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $existingUser = User::query()
            ->where('email', $data['email'])
            ->first();

        if ($existingUser && $existingUser->is_verified) {
            throw ValidationException::withMessages([
                'email' => ['Este correo ya esta registrado.'],
            ]);
        }

        $user = DB::transaction(function () use ($data, $existingUser): User {
            if ($existingUser) {
                $existingUser->forceFill([
                    'name' => $data['name'],
                    'phone' => $data['phone'] ?? null,
                    'role' => 'customer',
                    'is_active' => false,
                    'is_verified' => false,
                    'email_verified_at' => null,
                    'password' => Hash::make($data['password']),
                ])->save();

                return $existingUser->fresh();
            }

            return User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'role' => 'customer',
                'is_active' => false,
                'is_verified' => false,
                'email_verified_at' => null,
                'password' => Hash::make($data['password']),
            ]);
        });

        $otpService->sendForUser($user);

        return response()->json([
            'message' => 'Te enviamos un codigo de verificacion a tu correo. Ingresa el codigo para activar tu cuenta.',
            'user' => $user,
            'requires_verification' => true,
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

        if (! $user->is_verified) {
            LoginHistory::create(array_merge($historyData, ['user_id' => $user->id]));
            throw ValidationException::withMessages([
                'email' => ['Debes verificar tu correo con el codigo OTP antes de iniciar sesion.'],
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

    public function google(Request $request, GoogleIdentityService $googleIdentityService): JsonResponse
    {
        $data = $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        $googleUser = $googleIdentityService->verifyIdToken($data['id_token']);

        if (! $googleUser['email_verified']) {
            throw ValidationException::withMessages([
                'google' => ['La cuenta de Google no tiene el correo verificado.'],
            ]);
        }

        $user = DB::transaction(function () use ($googleUser): User {
            $user = User::query()
                ->where('google_id', $googleUser['sub'])
                ->orWhere('email', $googleUser['email'])
                ->first();

            if ($user) {
                $wasVerified = (bool) $user->is_verified;
                $user->forceFill([
                    'google_id' => $googleUser['sub'],
                    'name' => $googleUser['name'],
                    'avatar_url' => $googleUser['picture'],
                    'email_verified_at' => now(),
                    'is_verified' => true,
                    'is_active' => $user->is_active || ! $wasVerified,
                    'otp_code' => null,
                    'otp_expires_at' => null,
                ])->save();

                return $user->fresh();
            }

            return User::create([
                'name' => $googleUser['name'],
                'email' => $googleUser['email'],
                'google_id' => $googleUser['sub'],
                'avatar_url' => $googleUser['picture'],
                'phone' => null,
                'role' => 'customer',
                'is_active' => true,
                'is_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(40)),
            ]);
        });

        if (! $user->is_active) {
            LoginHistory::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'successful' => false,
            ]);

            throw ValidationException::withMessages([
                'google' => ['Cuenta desactivada. Contacta con soporte de Pollos y Parrillas El Dorado.'],
            ]);
        }

        LoginHistory::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'successful' => true,
        ]);

        return response()->json([
            'token' => JwtService::encode($user),
            'user' => $user,
            'message' => 'Sesion iniciada con Google.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}
