<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\Mail\ResendEmailService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OtpService
{
    public function __construct(
        private readonly ResendEmailService $resendEmailService,
    ) {
    }

    public function sendForUser(User $user): string
    {
        $code = $this->generateCode();

        $user->forceFill([
            'otp_code' => Hash::make($code),
            'otp_expires_at' => now()->addMinutes(10),
        ])->save();

        $text = $this->buildTextMessage($user, $code);
        $html = $this->buildHtmlMessage($user, $code);

        if ($this->resendEmailService->enabled()) {
            try {
                $this->resendEmailService->send([
                    'to' => [$user->email],
                    'subject' => 'Tu codigo de verificación',
                    'html' => $html,
                    'text' => $text,
                ]);

                return $code;
            } catch (Throwable $exception) {
                Log::warning('No se pudo enviar OTP con Resend. Se usara el mailer de Laravel.', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        Mail::raw(
            $text,
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

    private function buildTextMessage(User $user, string $code): string
    {
        $name = trim((string) $user->name) !== '' ? $user->name : 'usuario';

        return implode("\n\n", [
            "Un gusto de ver tu preferencia hacia nosotros {$name},",
            "Tu codigo para confirmar tu cuenta es: {$code}",
            'Este codigo vence en 10 minutos.',
            'Si no solicitaste este codigo, ignoralo.',
        ]);
    }

    private function buildHtmlMessage(User $user, string $code): string
    {
        $name = e(trim((string) $user->name) !== '' ? $user->name : 'usuario');
        $brand = e((string) config('app.name', 'Tu tienda'));
        $safeCode = e($code);

        return <<<HTML
<div style="font-family:Arial,sans-serif;background:#f8fafc;padding:24px;color:#111827;">
  <div style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:16px;padding:32px;border:1px solid #e5e7eb;">
    <p style="margin:0 0 12px;font-size:14px;color:#6b7280;">{$brand}</p>
    <h1 style="margin:0 0 16px;font-size:24px;">Verificacion de correo</h1>
    <p style="margin:0 0 20px;font-size:16px;">Un gusto de ver tu preferencia hacia nosotros {$name},</p>
    <p style="margin:0 0 20px;font-size:16px;">Tu codigo para confirmar tu cuenta es:</p>
    <div style="margin:0 0 20px;padding:16px 20px;background:#111827;color:#ffffff;border-radius:12px;font-size:32px;font-weight:700;letter-spacing:8px;text-align:center;">{$safeCode}</div>
    <p style="margin:0 0 8px;font-size:15px;">Este codigo vence en 10 minutos.</p>
    <p style="margin:0;font-size:14px;color:#6b7280;">Si no solicitaste este codigo, ignoralo.</p>
  </div>
</div>
HTML;
    }
}
