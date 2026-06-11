<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\Mail\CustomerLifecycleEmailService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetService
{
    public function __construct(
        private readonly CustomerLifecycleEmailService $customerLifecycleEmailService,
    ) {
    }

    public function sendResetLink(string $email): void
    {
        $user = User::query()
            ->where('email', $email)
            ->first();

        if (! $user || ! $user->is_active || ! $user->is_verified) {
            return;
        }

        $token = Password::broker()->createToken($user);
        $resetUrl = url('/reset-password?token='.urlencode($token).'&email='.urlencode($user->email));

        $this->customerLifecycleEmailService->sendPasswordReset($user, $resetUrl);
    }

    public function resetPassword(string $email, string $token, string $password): array
    {
        $status = Password::broker()->reset(
            [
                'email' => $email,
                'token' => $token,
                'password' => $password,
                'password_confirmation' => $password,
            ],
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return [
                'ok' => false,
                'message' => 'El enlace de recuperacion no es valido o ya vencio.',
            ];
        }

        return [
            'ok' => true,
            'message' => 'Tu contrasena fue actualizada correctamente.',
        ];
    }
}
