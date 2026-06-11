<?php

namespace App\Services\Mail;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ResendEmailService
{
    public function enabled(): bool
    {
        return trim((string) config('services.resend.key')) !== '';
    }

    public function send(array $message): void
    {
        if (! $this->enabled()) {
            throw new RuntimeException('Resend no esta configurado.');
        }

        $from = $message['from'] ?? $this->defaultFrom();

        if ($from === '') {
            throw new RuntimeException('No hay direccion remitente configurada para Resend.');
        }

        Http::baseUrl('https://api.resend.com')
            ->withToken((string) config('services.resend.key'))
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('services.resend.timeout', 15))
            ->post('/emails', [
                'from' => $from,
                'to' => array_values(array_filter((array) ($message['to'] ?? []))),
                'subject' => (string) ($message['subject'] ?? ''),
                'html' => $message['html'] ?? null,
                'text' => $message['text'] ?? null,
                'attachments' => $message['attachments'] ?? null,
            ])
            ->throw();
    }

    private function defaultFrom(): string
    {
        $address = trim((string) config('services.resend.from_address', config('mail.from.address')));
        $name = trim((string) config('services.resend.from_name', config('mail.from.name')));

        if ($address === '') {
            return '';
        }

        return $name !== '' ? "{$name} <{$address}>" : $address;
    }
}
