<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\OtpService;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthOtpController extends Controller
{
    public function verify(Request $request, OtpService $otpService): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = User::query()->where('email', $data['email'])->first();

        if (! $user) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        if (! $otpService->verify($user, $data['code'])) {
            return response()->json(['message' => 'Codigo OTP invalido o expirado.'], 422);
        }

        $user->forceFill([
            'is_active' => true,
            'is_verified' => true,
            'email_verified_at' => now(),
        ])->save();

        $otpService->clear($user);

        return response()->json([
            'message' => 'Correo verificado correctamente.',
            'user' => $user->fresh(),
            'token' => JwtService::encode($user->fresh()),
        ]);
    }

    public function resend(Request $request, OtpService $otpService): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::query()->where('email', $data['email'])->first();

        if (! $user) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        if ($user->is_verified) {
            return response()->json(['message' => 'Este correo ya esta verificado.'], 422);
        }

        try {
            $otpService->sendForUser($user);
        } catch (\Throwable $exception) {
            Log::error('No se pudo reenviar OTP', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'No pudimos reenviar el codigo de verificacion. Revisa la configuracion de correo e intenta nuevamente.',
            ], 503);
        }

        return response()->json([
            'message' => 'Codigo OTP reenviado correctamente.',
        ]);
    }
}
