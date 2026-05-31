<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;
use RuntimeException;

class PeruRegistryLookupService
{
    public function lookupDni(string $dni): array
    {
        $this->ensureConfigured('reniec');

        $response = $this->requestLookup('reniec', ['dni' => $dni, 'numero' => $dni]);

        $data = $this->decodeResponse($response, 'RENIEC');
        $normalized = [
            'document_number' => (string) $this->firstFilled($data, ['dni', 'numeroDocumento', 'numero', 'documento', 'document_number'], $dni),
            'full_name' => $this->normalizeFullName($data),
            'first_names' => $this->firstFilled($data, ['nombres', 'nombresCompletos', 'prenombres', 'name', 'nombres_persona']),
            'paternal_surname' => $this->firstFilled($data, ['apellidoPaterno', 'apellido_paterno', 'apePaterno']),
            'maternal_surname' => $this->firstFilled($data, ['apellidoMaterno', 'apellido_materno', 'apeMaterno']),
            'check_digit' => $this->firstFilled($data, ['codVerifica', 'digitoVerificador', 'check_digit']),
        ];

        if (trim((string) ($normalized['full_name'] ?? '')) === '') {
            throw new RuntimeException($this->extractProviderMessage($data, 'No se encontro informacion valida para ese DNI.'));
        }

        return [
            'provider' => 'reniec',
            'document_type' => 'dni',
            'document_number' => $dni,
            'raw' => $data,
            'normalized' => $normalized,
        ];
    }

    public function lookupRuc(string $ruc): array
    {
        $this->ensureConfigured('sunat');

        $response = $this->requestLookup('sunat', ['ruc' => $ruc, 'numero' => $ruc], $this->sunatToken());

        $data = $this->decodeResponse($response, 'SUNAT');
        $normalized = [
            'document_number' => (string) $this->firstFilled($data, ['ruc', 'numeroDocumento', 'numero', 'document_number'], $ruc),
            'business_name' => $this->firstFilled($data, ['razonSocial', 'nombreOrazonSocial', 'nombreoRazonSocial', 'business_name', 'nombre']),
            'trade_name' => $this->firstFilled($data, ['nombreComercial', 'trade_name']),
            'status' => $this->firstFilled($data, ['estado', 'status']),
            'condition' => $this->firstFilled($data, ['condicion', 'condition']),
            'address' => $this->firstFilled($data, ['direccion', 'domicilioFiscal', 'address']),
            'department' => $this->firstFilled($data, ['departamento', 'department']),
            'province' => $this->firstFilled($data, ['provincia', 'province']),
            'district' => $this->firstFilled($data, ['distrito', 'district']),
            'ubigeo' => $this->firstFilled($data, ['ubigeo']),
        ];

        if (trim((string) ($normalized['business_name'] ?? '')) === '') {
            throw new RuntimeException($this->extractProviderMessage($data, 'No se encontro informacion valida para ese RUC.'));
        }

        return [
            'provider' => 'sunat',
            'document_type' => 'ruc',
            'document_number' => $ruc,
            'raw' => $data,
            'normalized' => $normalized,
        ];
    }

    private function ensureConfigured(string $provider): void
    {
        $url = trim($this->providerLookupUrl($provider));
        if ($url === '') {
            throw new RuntimeException("La integracion {$provider} no esta configurada en el servidor. Configura APIsPeru o define {$this->providerEnvKey($provider)}.");
        }

        $authMode = $this->providerAuthMode($provider);
        $token = trim($this->providerToken($provider));
        if ($authMode === 'query' && $token === '') {
            throw new RuntimeException('Falta configurar APISPERU_DNIRUC_TOKEN o el token del proveedor contratado.');
        }
    }

    private function sunatToken(): string
    {
        $staticToken = trim((string) config('services.sunat.token'));
        if ($staticToken !== '') {
            return $staticToken;
        }

        $tokenUrl = trim((string) config('services.sunat.token_url'));
        $grantType = trim((string) config('services.sunat.grant_type', 'password'));
        $clientId = trim((string) config('services.sunat.client_id'));
        $clientSecret = trim((string) config('services.sunat.client_secret'));
        $username = trim((string) config('services.sunat.username'));
        $password = trim((string) config('services.sunat.password'));

        if ($tokenUrl === '' || $clientId === '' || $clientSecret === '') {
            return '';
        }

        return Cache::remember('sunat_api_token', now()->addMinutes(50), function () use ($tokenUrl, $grantType, $clientId, $clientSecret, $username, $password): string {
            if ($grantType === 'password' && ($username === '' || $password === '')) {
                throw new RuntimeException('SUNAT requiere usuario SOL y clave SOL para generar el token.');
            }

            $request = Http::asForm()
                ->timeout((int) config('services.sunat.timeout', 15))
                ->acceptJson()
                ->post($tokenUrl, array_filter([
                    'grant_type' => $grantType,
                    'scope' => trim((string) config('services.sunat.scope')),
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'username' => $grantType === 'password' ? $username : null,
                    'password' => $grantType === 'password' ? $password : null,
                ], static fn ($value): bool => $value !== ''));

            if ($request->failed()) {
                throw new RuntimeException('No se pudo obtener el token OAuth de SUNAT.');
            }

            $accessToken = (string) data_get($request->json(), 'access_token', '');
            if ($accessToken === '') {
                throw new RuntimeException('SUNAT no devolvio access_token.');
            }

            return $accessToken;
        });
    }

    private function requestLookup(string $provider, array $params, ?string $token = null)
    {
        $request = Http::timeout($this->providerTimeout($provider))
            ->acceptJson()
            ->withHeaders([
                'User-Agent' => 'PollosElDorado/1.0',
            ]);

        $authMode = $this->providerAuthMode($provider);
        $authToken = trim((string) ($token ?? $this->providerToken($provider)));
        $query = [];

        if ($authToken !== '') {
            if ($authMode === 'query') {
                $query[$this->providerTokenQueryParam($provider)] = $authToken;
            } else {
                $request = $request->withToken($authToken);
            }
        }

        try {
            return $request->get($this->buildLookupUrl($provider, $params), $query);
        } catch (Throwable $exception) {
            throw new RuntimeException('No se pudo conectar con APIs Perú para consultar el documento.');
        }
    }

    private function buildLookupUrl(string $provider, array $params): string
    {
        $url = $this->providerLookupUrl($provider);

        foreach ($params as $key => $value) {
            $url = str_replace('{'.$key.'}', urlencode((string) $value), $url);
        }

        return $url;
    }

    private function providerLookupUrl(string $provider): string
    {
        $configured = trim((string) config("services.{$provider}.lookup_url"));
        if ($configured !== '') {
            return $configured;
        }

        $baseUrl = trim((string) config('services.apisperu_dniruc.base_url', ''));
        if ($baseUrl === '') {
            return '';
        }

        return match ($provider) {
            'reniec' => rtrim($baseUrl, '/').'/dni/{numero}',
            'sunat' => rtrim($baseUrl, '/').'/ruc/{numero}',
            default => '',
        };
    }

    private function providerToken(string $provider): string
    {
        $configured = trim((string) config("services.{$provider}.token"));
        if ($configured !== '') {
            return $configured;
        }

        return trim((string) config('services.apisperu_dniruc.token', ''));
    }

    private function providerAuthMode(string $provider): string
    {
        $configured = trim((string) config("services.{$provider}.auth_mode"));
        if ($configured !== '') {
            return $configured;
        }

        return trim((string) config('services.apisperu_dniruc.auth_mode', 'query'));
    }

    private function providerTokenQueryParam(string $provider): string
    {
        $configured = trim((string) config("services.{$provider}.token_query_param"));
        if ($configured !== '') {
            return $configured;
        }

        return trim((string) config('services.apisperu_dniruc.token_query_param', 'token'));
    }

    private function providerTimeout(string $provider): int
    {
        $configured = (int) config("services.{$provider}.timeout", 0);
        if ($configured > 0) {
            return $configured;
        }

        return (int) config('services.apisperu_dniruc.timeout', 15);
    }

    private function providerEnvKey(string $provider): string
    {
        return $provider === 'reniec' ? 'RENIEC_LOOKUP_URL' : 'SUNAT_LOOKUP_URL';
    }

    private function firstFilled(array $data, array $keys, mixed $default = null): mixed
    {
        foreach ($keys as $key) {
            $value = data_get($data, $key);
            if ($value !== null && trim((string) $value) !== '') {
                return $value;
            }
        }

        return $default;
    }

    private function normalizeFullName(array $data): string
    {
        $direct = $this->firstFilled($data, [
            'nombreCompleto',
            'nombre_completo',
            'full_name',
            'nombre',
        ]);

        if ($direct !== null) {
            return trim((string) $direct);
        }

        return trim(implode(' ', array_filter([
            $this->firstFilled($data, ['nombres', 'prenombres']),
            $this->firstFilled($data, ['apellidoPaterno', 'apellido_paterno', 'apePaterno']),
            $this->firstFilled($data, ['apellidoMaterno', 'apellido_materno', 'apeMaterno']),
        ], static fn ($value): bool => trim((string) $value) !== '')));
    }

    private function decodeResponse($response, string $provider): array
    {
        if ($response->status() === 401 || $response->status() === 403) {
            throw new RuntimeException('El token de APIs Perú no es valido o ya no tiene acceso.');
        }

        if ($response->status() === 404) {
            throw new RuntimeException("No se encontro informacion para ese {$provider}.");
        }

        $data = $response->json();

        if (! is_array($data)) {
            $raw = trim((string) $response->body());

            if ($raw !== '') {
                throw new RuntimeException($raw);
            }

            throw new RuntimeException("{$provider} devolvio una respuesta invalida.");
        }

        if ($response->failed()) {
            throw new RuntimeException($this->extractProviderMessage($data, "No se pudo consultar {$provider} en este momento."));
        }

        $success = data_get($data, 'success');
        if ($success === false || $success === 0 || $success === 'false') {
            throw new RuntimeException($this->extractProviderMessage($data, "La consulta a {$provider} fue rechazada."));
        }

        return $data;
    }

    private function extractProviderMessage(array $data, string $fallback): string
    {
        $message = $this->firstFilled($data, [
            'message',
            'mensaje',
            'error',
            'detail',
            'details',
            'errors.0.message',
            'errors.0',
            'data.message',
        ]);

        return trim((string) ($message ?? '')) !== '' ? trim((string) $message) : $fallback;
    }
}
