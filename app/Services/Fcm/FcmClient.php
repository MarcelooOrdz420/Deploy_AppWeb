<?php

namespace App\Services\Fcm;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FcmClient
{
    public function isConfigured(): bool
    {
        return $this->serviceAccountProjectId() !== '' && $this->serviceAccountPrivateKey() !== '' && $this->serviceAccountClientEmail() !== '';
    }

    public function sendToTopic(string $topic, array $notification, array $data = []): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('FCM no está configurado. Falta FCM_SERVICE_ACCOUNT_PATH/FCM_SERVICE_ACCOUNT_JSON o campos del service account.');
        }

        $projectId = $this->projectId();
        $accessToken = $this->accessToken();

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $payload = [
            'message' => [
                'topic' => $topic,
                'notification' => array_filter([
                    'title' => (string) ($notification['title'] ?? ''),
                    'body' => (string) ($notification['body'] ?? ''),
                    'image' => $notification['image'] ?? null,
                ], fn ($v) => $v !== null && trim((string) $v) !== ''),
                'data' => $this->stringData($data),
                'android' => [
                    'priority' => 'HIGH',
                    'notification' => [
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'content-available' => 1,
                        ],
                    ],
                ],
            ],
        ];

        $res = Http::withToken($accessToken)
            ->acceptJson()
            ->post($url, $payload);

        $json = $res->json();
        if (! $res->ok()) {
            $msg = is_array($json) ? json_encode($json) : (string) $res->body();
            throw new RuntimeException("FCM error ({$res->status()}): {$msg}");
        }

        return is_array($json) ? $json : ['ok' => true];
    }

    private function stringData(array $data): array
    {
        $out = [];
        foreach ($data as $k => $v) {
            if ($v === null) continue;
            $out[(string) $k] = is_scalar($v) ? (string) $v : json_encode($v);
        }
        return $out;
    }

    private function projectId(): string
    {
        $env = trim((string) env('FCM_PROJECT_ID', ''));
        if ($env !== '') return $env;
        return $this->serviceAccountProjectId();
    }

    private function accessToken(): string
    {
        $cacheKey = 'fcm.access_token';
        $cached = (string) Cache::get($cacheKey, '');
        if ($cached !== '') return $cached;

        $jwt = $this->makeJwt();
        $res = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        $json = $res->json();
        if (! $res->ok()) {
            $msg = is_array($json) ? json_encode($json) : (string) $res->body();
            throw new RuntimeException("OAuth token error ({$res->status()}): {$msg}");
        }

        $token = (string) ($json['access_token'] ?? '');
        $expiresIn = (int) ($json['expires_in'] ?? 3600);
        if ($token === '') throw new RuntimeException('OAuth token vacío.');

        Cache::put($cacheKey, $token, max(60, $expiresIn - 120));
        return $token;
    }

    private function makeJwt(): string
    {
        $now = time();
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $claims = [
            'iss' => $this->serviceAccountClientEmail(),
            'sub' => $this->serviceAccountClientEmail(),
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        ];

        $segments = [
            $this->base64UrlEncode(json_encode($header)),
            $this->base64UrlEncode(json_encode($claims)),
        ];
        $input = implode('.', $segments);

        $signature = '';
        $privateKey = openssl_pkey_get_private($this->serviceAccountPrivateKey());
        if (! $privateKey) throw new RuntimeException('Private key inválida para FCM.');

        $ok = openssl_sign($input, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        if (! $ok) throw new RuntimeException('No se pudo firmar JWT para FCM.');

        $segments[] = $this->base64UrlEncode($signature);
        return implode('.', $segments);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function serviceAccount(): array
    {
        $json = trim((string) env('FCM_SERVICE_ACCOUNT_JSON', ''));
        if ($json === '') {
            $path = trim((string) env('FCM_SERVICE_ACCOUNT_PATH', ''));
            if ($path === '') return [];
            if (! file_exists($path)) return [];
            $json = (string) file_get_contents($path);
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function serviceAccountProjectId(): string
    {
        return trim((string) ($this->serviceAccount()['project_id'] ?? ''));
    }

    private function serviceAccountClientEmail(): string
    {
        return trim((string) ($this->serviceAccount()['client_email'] ?? ''));
    }

    private function serviceAccountPrivateKey(): string
    {
        return (string) ($this->serviceAccount()['private_key'] ?? '');
    }
}

