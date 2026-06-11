<?php

namespace App\Services\Mail;

use App\Models\CartRecovery;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Throwable;

class CustomerLifecycleEmailService
{
    public function __construct(
        private readonly ResendEmailService $resendEmailService,
    ) {
    }

    public function sendPasswordReset(User $user, string $resetUrl): void
    {
        $brand = e((string) config('company.brand_name', config('app.name')));
        $name = e($user->name ?: 'cliente');
        $safeUrl = e($resetUrl);

        $subject = 'Recupera tu contrasena';
        $text = implode("\n\n", [
            "Hola {$user->name},",
            'Recibimos una solicitud para cambiar tu contrasena.',
            "Usa este enlace para crear una nueva: {$resetUrl}",
            'Si no solicitaste este cambio, puedes ignorar este correo.',
        ]);

        $html = <<<HTML
<div style="font-family:Arial,sans-serif;background:#f8fafc;padding:24px;color:#111827;">
  <div style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:16px;padding:32px;border:1px solid #e5e7eb;">
    <p style="margin:0 0 12px;font-size:14px;color:#6b7280;">{$brand}</p>
    <h1 style="margin:0 0 16px;font-size:24px;">Recupera tu contrasena</h1>
    <p style="margin:0 0 18px;font-size:16px;">Hola {$name}, recibimos una solicitud para cambiar tu contrasena.</p>
    <p style="margin:0 0 22px;font-size:15px;">Haz clic en el siguiente boton para elegir una nueva.</p>
    <p style="margin:0 0 22px;">
      <a href="{$safeUrl}" style="display:inline-block;padding:14px 22px;border-radius:999px;background:#f97316;color:#ffffff;text-decoration:none;font-weight:700;">Cambiar contrasena</a>
    </p>
    <p style="margin:0 0 10px;font-size:14px;color:#6b7280;">Si el boton no abre, copia este enlace en tu navegador:</p>
    <p style="margin:0;font-size:13px;word-break:break-all;color:#374151;">{$safeUrl}</p>
  </div>
</div>
HTML;

        $this->deliver(
            to: $user->email,
            name: $user->name,
            subject: $subject,
            html: $html,
            text: $text,
        );
    }

    public function sendPromotion(User $user, array $campaign): void
    {
        $brand = e((string) config('company.brand_name', config('app.name')));
        $name = e($user->name ?: 'cliente');
        $title = e((string) ($campaign['title'] ?? 'Nueva promocion'));
        $body = e((string) ($campaign['body'] ?? $campaign['message'] ?? 'Tenemos novedades para ti.'));
        $message = e((string) ($campaign['message'] ?? ''));
        $imageUrl = trim((string) ($campaign['image_url'] ?? ''));
        $ctaLabel = trim((string) ($campaign['cta_label'] ?? 'Ver promo'));
        $ctaUrl = url('/productos');
        $safeCtaUrl = e($ctaUrl);
        $safeImageUrl = e($imageUrl);
        $safeCtaLabel = e($ctaLabel !== '' ? $ctaLabel : 'Ver promo');

        $subject = trim((string) ($campaign['email_subject'] ?? $campaign['title'] ?? 'Nueva promocion'));
        $text = implode("\n\n", array_filter([
            "Hola {$user->name},",
            strip_tags((string) ($campaign['message'] ?? '')),
            strip_tags((string) ($campaign['body'] ?? '')),
            "Ver promocion: {$ctaUrl}",
        ]));

        $imageBlock = $imageUrl !== ''
            ? '<p style="margin:0 0 18px;"><img src="'.$safeImageUrl.'" alt="'.$title.'" style="width:100%;max-width:496px;border-radius:16px;display:block;"></p>'
            : '';

        $html = <<<HTML
<div style="font-family:Arial,sans-serif;background:#fff7ed;padding:24px;color:#111827;">
  <div style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:18px;padding:32px;border:1px solid #fed7aa;">
    <p style="margin:0 0 12px;font-size:14px;color:#9a3412;">{$brand}</p>
    <p style="margin:0 0 8px;font-size:15px;">Hola {$name},</p>
    <h1 style="margin:0 0 14px;font-size:26px;color:#7c2d12;">{$title}</h1>
    {$imageBlock}
    <p style="margin:0 0 10px;font-size:16px;">{$message}</p>
    <p style="margin:0 0 20px;font-size:15px;color:#57534e;">{$body}</p>
    <p style="margin:0;">
      <a href="{$safeCtaUrl}" style="display:inline-block;padding:14px 22px;border-radius:999px;background:#ea580c;color:#ffffff;text-decoration:none;font-weight:700;">{$safeCtaLabel}</a>
    </p>
  </div>
</div>
HTML;

        $this->deliver(
            to: $user->email,
            name: $user->name,
            subject: $subject !== '' ? $subject : 'Nueva promocion',
            html: $html,
            text: $text,
        );
    }

    public function sendInactiveRecovery(User $user, ?Carbon $lastSeenAt): void
    {
        $brand = e((string) config('company.brand_name', config('app.name')));
        $name = e($user->name ?: 'cliente');
        $productsUrl = e(url('/productos'));
        $lastSeenText = $lastSeenAt ? $lastSeenAt->timezone(config('app.timezone'))->format('d/m/Y H:i') : 'hace varios dias';
        $subject = 'Te estamos esperando de vuelta';

        $text = implode("\n\n", [
            "Hola {$user->name},",
            "Te extraniamos. Tu ultima actividad fue {$lastSeenText}.",
            'Vuelve a la tienda y descubre nuestras promos y productos disponibles.',
            "Entrar ahora: ".url('/productos'),
        ]);

        $html = <<<HTML
<div style="font-family:Arial,sans-serif;background:#f8fafc;padding:24px;color:#111827;">
  <div style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:18px;padding:32px;border:1px solid #e2e8f0;">
    <p style="margin:0 0 12px;font-size:14px;color:#64748b;">{$brand}</p>
    <h1 style="margin:0 0 14px;font-size:26px;">{$name}, te estamos esperando de vuelta</h1>
    <p style="margin:0 0 12px;font-size:16px;">Notamos que no inicias sesion desde {$lastSeenText}.</p>
    <p style="margin:0 0 20px;font-size:15px;color:#475569;">Vuelve a ver nuestras novedades, promociones y el estado de tus productos favoritos.</p>
    <p style="margin:0;">
      <a href="{$productsUrl}" style="display:inline-block;padding:14px 22px;border-radius:999px;background:#f97316;color:#ffffff;text-decoration:none;font-weight:700;">Volver a la tienda</a>
    </p>
  </div>
</div>
HTML;

        $this->deliver(
            to: $user->email,
            name: $user->name,
            subject: $subject,
            html: $html,
            text: $text,
        );
    }

    public function sendAbandonedCart(CartRecovery $cartRecovery): void
    {
        $brand = e((string) config('company.brand_name', config('app.name')));
        $name = e($cartRecovery->customer_name ?: 'cliente');
        $cartUrl = e(url('/carrito'));
        $subtotal = number_format((float) $cartRecovery->subtotal_amount, 2, '.', '');

        $items = collect($cartRecovery->items ?? [])
            ->take(5)
            ->map(function (array $item): string {
                $name = e((string) ($item['name'] ?? 'Producto'));
                $qty = (int) ($item['qty'] ?? 0);
                $line = number_format((float) ($item['line_total'] ?? 0), 2, '.', '');

                return "<li style=\"margin:0 0 8px;\">{$name} x{$qty} - S/ {$line}</li>";
            })
            ->implode('');

        $subject = 'Tu carrito sigue esperandote';
        $text = implode("\n\n", [
            "Hola {$cartRecovery->customer_name},",
            'Guardamos los productos que dejaste en tu carrito para que contines tu compra.',
            "Subtotal guardado: S/ {$subtotal}",
            "Retomar compra: ".url('/carrito'),
        ]);

        $html = <<<HTML
<div style="font-family:Arial,sans-serif;background:#fff7ed;padding:24px;color:#111827;">
  <div style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:18px;padding:32px;border:1px solid #fdba74;">
    <p style="margin:0 0 12px;font-size:14px;color:#9a3412;">{$brand}</p>
    <h1 style="margin:0 0 14px;font-size:26px;">{$name}, tu carrito sigue esperandote</h1>
    <p style="margin:0 0 18px;font-size:16px;">Guardamos los productos que dejaste a medio camino para que puedas terminar tu compra sin empezar de cero.</p>
    <ul style="margin:0 0 18px 18px;padding:0;color:#44403c;">{$items}</ul>
    <p style="margin:0 0 18px;font-size:15px;"><strong>Subtotal guardado:</strong> S/ {$subtotal}</p>
    <p style="margin:0;">
      <a href="{$cartUrl}" style="display:inline-block;padding:14px 22px;border-radius:999px;background:#ea580c;color:#ffffff;text-decoration:none;font-weight:700;">Retomar compra</a>
    </p>
  </div>
</div>
HTML;

        $this->deliver(
            to: $cartRecovery->email,
            name: $cartRecovery->customer_name,
            subject: $subject,
            html: $html,
            text: $text,
        );
    }

    private function deliver(string $to, ?string $name, string $subject, string $html, string $text): void
    {
        if (trim($to) === '') {
            return;
        }

        if ($this->resendEmailService->enabled()) {
            $this->resendEmailService->send([
                'to' => [$to],
                'subject' => $subject,
                'html' => $html,
                'text' => $text,
            ]);

            return;
        }

        try {
            Mail::html($html, function ($message) use ($to, $name, $subject): void {
                $message->to($to, $name ?: null)
                    ->subject($subject);
            });
        } catch (Throwable) {
            Mail::raw($text, function ($message) use ($to, $name, $subject): void {
                $message->to($to, $name ?: null)
                    ->subject($subject);
            });
        }
    }
}
