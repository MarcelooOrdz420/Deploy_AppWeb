<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    public function sendForUser(User $user): string
    {
        $code = $this->generateCode();

        $user->forceFill([
            'otp_code' => Hash::make($code),
            'otp_expires_at' => now()->addMinutes(10),
        ])->save();

        Mail::raw(
            $this->buildMessage($user, $code),
            function ($message) use ($user): void {
                $message->to($user->email, $user->name)
                    ->subject('Tu codigo de verificacion');
            }
        );

        return $code;
    }

    public function verify(User $user, string $code): bool
    {
        if (! $user->otp_code || ! $user->otp_expires_at) {
            return false;
        }

        if ($user->otp_expires_at->isPast()) {
            return false;
        }

        return Hash::check($code, $user->otp_code);
    }

    public function clear(User $user): void
    {
        $user->forceFill([
            'otp_code' => null,
            'otp_expires_at' => null,
        ])->save();
    }

    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function buildMessage(User $user, string $code): string
    {
        $name = trim((string) $user->name) !== '' ? $user->name : 'usuario';

        return implode("\n\n", [
            "Hola {$name},",
            "Tu codigo OTP de verificacion es: {$code}",
            'Este codigo vence en 10 minutos.',
            'Si no solicitaste este codigo, ignora este correo.',
        ]);
    }
}
