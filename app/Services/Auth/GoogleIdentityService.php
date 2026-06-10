<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleIdentityService
{
    /**
     * @return array{sub:string,email:string,name:string,picture:?string,email_verified:bool,aud:string}
     */
    public function verifyIdToken(string $idToken): array
    {
        $token = trim($idToken);

        if ($token === '') {
            throw new RuntimeException('Google no devolvio un token valido.');
        }

        $response = Http::timeout(15)
            ->acceptJson()
            ->get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $token,
            ]);

        if (! $response->ok()) {
            throw new RuntimeException('No se pudo validar la identidad con Google.');
        }

        /** @var array<string, mixed> $payload */
        $payload = $response->json() ?? [];

        $audience = trim((string) ($payload['aud'] ?? ''));
        $allowedClientIds = config('services.google_auth.client_ids', []);

        if ($audience === '' || ! in_array($audience, $allowedClientIds, true)) {
            throw new RuntimeException('El cliente de Google no coincide con la configuracion esperada.');
        }

        $email = trim((string) ($payload['email'] ?? ''));
        $sub = trim((string) ($payload['sub'] ?? ''));

        if ($email === '' || $sub === '') {
            throw new RuntimeException('Google no devolvio los datos minimos de la cuenta.');
        }

        return [
            'sub' => $sub,
            'email' => $email,
            'name' => trim((string) ($payload['name'] ?? '')) ?: strstr($email, '@', true) ?: 'Cliente El Dorado',
            'picture' => ($picture = trim((string) ($payload['picture'] ?? ''))) !== '' ? $picture : null,
            'email_verified' => filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOL),
            'aud' => $audience,
        ];
    }
}

