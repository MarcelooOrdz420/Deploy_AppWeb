<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\Mail\CustomerLifecycleEmailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetService
{
    public function __construct(
        private readonly CustomerLifecycleEmailService $customerLifecycleEmailService,
    ) {
    }

    public function sendResetLink(string $email): array
    {
        $user = User::query()
            ->where('email', $email)
            ->first();

        if (! $user || ! $user->is_active || ! $user->is_verified) {
            return $this->genericResponse();
        }

        if (! empty($user->google_id)) {
            return [
                'ok' => false,
                'status' => 422,
                'message' => 'Esta cuenta usa Google. Ingresa con Google y no cambies la contrasena desde aqui.',
            ];
        }

        $expiryMinutes = (int) config('auth.passwords.users.expire', 60);
        $throttleSeconds = (int) config('auth.passwords.users.throttle', 60);
        $now = now();
        $pendingUntil = $user->password_reset_requested_at?->copy()->addMinutes($expiryMinutes);

        if ($user->password_reset_requested_at && $pendingUntil && $pendingUntil->isFuture()) {
            return [
                'ok' => false,
                'status' => 429,
                'message' => 'Ya enviamos un enlace de recuperacion. Termina ese cambio de clave o espera a que venza el enlace actual.',
                'pending_until' => $pendingUntil->toIso8601String(),
            ];
        }

        if ($user->password_reset_requested_at && $user->password_reset_requested_at->copy()->addSeconds($throttleSeconds)->isFuture()) {
            return [
                'ok' => false,
                'status' => 429,
                'message' => 'Espera un momento antes de volver a solicitar otro enlace de recuperacion.',
            ];
        }

        DB::table((string) config('auth.passwords.users.table', 'password_reset_tokens'))
            ->where('email', $user->email)
            ->delete();

        $token = Password::broker()->createToken($user);
        $resetUrl = url('/reset-password?token='.urlencode($token).'&email='.urlencode($user->email));

        $this->customerLifecycleEmailService->sendPasswordReset($user, $resetUrl);

        $user->forceFill([
            'password_reset_requested_at' => $now,
        ])->save();

        return [
            'ok' => true,
            'status' => 200,
            'message' => 'Te enviamos un enlace de recuperacion a tu correo.',
            'pending_until' => $now->copy()->addMinutes($expiryMinutes)->toIso8601String(),
            'reset_email' => $user->email,
        ];
    }

    public function resetPassword(string $email, string $token, string $password): array
    {
        $user = User::query()->where('email', $email)->first();

        if ($user && ! empty($user->google_id)) {
            return [
                'ok' => false,
                'message' => 'Esta cuenta usa Google. Ingresa con Google y no cambies la contrasena desde aqui.',
            ];
        }

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
                    'password_reset_requested_at' => null,
                    'password_reset_completed_at' => now(),
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

    private function genericResponse(): array
    {
        return [
            'ok' => true,
            'status' => 200,
            'message' => 'Si el correo existe en nuestra base, te enviaremos un enlace para cambiar tu contrasena.',
        ];
    }
}
