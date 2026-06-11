<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class AuthPasswordController extends Controller
{
    public function forgot(Request $request, PasswordResetService $passwordResetService): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $passwordResetService->sendResetLink($data['email']);

        return response()->json([
            'message' => 'Si el correo existe en nuestra base, te enviaremos un enlace para cambiar tu contrasena.',
        ]);
    }

    public function reset(Request $request, PasswordResetService $passwordResetService): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $status = $passwordResetService->resetPassword(
            email: $data['email'],
            token: $data['token'],
            password: $data['password'],
        );

        if (! $status['ok']) {
            return response()->json([
                'message' => $status['message'],
            ], 422);
        }

        return response()->json([
            'message' => $status['message'],
        ]);
    }
}
