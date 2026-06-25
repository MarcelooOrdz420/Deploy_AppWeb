<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;
use RuntimeException;

class PeruRegistryLookupService
{
    public function lookupDni(string $dni): array
    {
        $this->ensureConfigured('apisperu_dni');

        $response = $this->requestLookup('apisperu_dni', ['dni' => $dni, 'numero' => $dni]);

        $data = $this->decodeResponse($response, 'APIs Peru');
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
            'provider' => 'apisperu_dniruc',
            'document_type' => 'dni',
            'document_number' => $dni,
            'raw' => $data,
            'normalized' => $normalized,
        ];
    }

    public function lookupRuc(string $ruc): array
    {
        if (! $this->isValidRuc($ruc)) {
            throw new RuntimeException('El RUC ingresado no es valido. Verifica los 11 digitos antes de consultar.');
        }

        $this->ensureConfigured('apisperu_ruc');

        $response = $this->requestLookup('apisperu_ruc', ['ruc' => $ruc, 'numero' => $ruc]);

        $data = $this->decodeResponse($response, 'APIs Peru');
        $normalized = [
            'document_number' => (string) $this->firstFilled($data, ['ruc', 'numeroDocumento', 'numero', 'document_number'], $ruc),
            'business_name' => $this->firstFilled($data, ['razonSocial', 'nombreOrazonSocial', 'nombreoRazonSocial', 'business_name', 'nombre', 'razon_social']),
            'trade_name' => $this->firstFilled($data, ['nombreComercial', 'trade_name', 'nombre_comercial']),
            'status' => $this->firstFilled($data, ['estado', 'status']),
            'condition' => $this->firstFilled($data, ['condicion', 'condition']),
            'address' => $this->firstFilled($data, ['direccion', 'domicilioFiscal', 'address', 'domicilio_fiscal']),
            'department' => $this->firstFilled($data, ['departamento', 'department']),
            'province' => $this->firstFilled($data, ['provincia', 'province']),
            'district' => $this->firstFilled($data, ['distrito', 'district']),
            'ubigeo' => $this->firstFilled($data, ['ubigeo']),
        ];

        if (trim((string) ($normalized['business_name'] ?? '')) === '') {
            throw new RuntimeException($this->extractProviderMessage($data, 'No se encontro informacion valida para ese RUC.'));
        }

        return [
            'provider' => 'apisperu_dniruc',
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
        if (str_starts_with($provider, 'apisperu_')) {
            $baseUrl = trim((string) config('services.apisperu_dniruc.base_url', ''));
            if ($baseUrl === '') {
                return '';
            }

            return match ($provider) {
                'apisperu_dni' => rtrim($baseUrl, '/').'/dni/{numero}',
                'apisperu_ruc' => rtrim($baseUrl, '/').'/ruc/{numero}',
                default => '',
            };
        }

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
        if (str_starts_with($provider, 'apisperu_')) {
            return trim((string) config('services.apisperu_dniruc.token', ''));
        }

        $configured = trim((string) config("services.{$provider}.token"));
        if ($configured !== '' && ! $this->isPlaceholderToken($configured)) {
            return $configured;
        }

        return trim((string) config('services.apisperu_dniruc.token', ''));
    }

    private function isPlaceholderToken(string $token): bool
    {
        $normalized = strtolower(trim($token));

        return in_array($normalized, [
            'tu_nuevo_token',
            'tu_token',
            'token',
            'your_token',
            'your_new_token',
        ], true);
    }

    private function providerAuthMode(string $provider): string
    {
        if (str_starts_with($provider, 'apisperu_')) {
            return trim((string) config('services.apisperu_dniruc.auth_mode', 'query'));
        }

        $configured = trim((string) config("services.{$provider}.auth_mode"));
        if ($configured !== '') {
            return $configured;
        }

        return trim((string) config('services.apisperu_dniruc.auth_mode', 'query'));
    }

    private function providerTokenQueryParam(string $provider): string
    {
        if (str_starts_with($provider, 'apisperu_')) {
            return trim((string) config('services.apisperu_dniruc.token_query_param', 'token'));
        }

        $configured = trim((string) config("services.{$provider}.token_query_param"));
        if ($configured !== '') {
            return $configured;
        }

        return trim((string) config('services.apisperu_dniruc.token_query_param', 'token'));
    }

    private function providerTimeout(string $provider): int
    {
        if (str_starts_with($provider, 'apisperu_')) {
            return (int) config('services.apisperu_dniruc.timeout', 15);
        }

        $configured = (int) config("services.{$provider}.timeout", 0);
        if ($configured > 0) {
            return $configured;
        }

        return (int) config('services.apisperu_dniruc.timeout', 15);
    }

    private function providerEnvKey(string $provider): string
    {
        if (str_starts_with($provider, 'apisperu_')) {
            return 'APISPERU_DNIRUC_BASE_URL';
        }

        return $provider === 'reniec' ? 'RENIEC_LOOKUP_URL' : 'SUNAT_LOOKUP_URL';
    }

    private function firstFilled(array $data, array $keys, mixed $default = null): mixed
    {
        foreach ($keys as $key) {
            $value = data_get($data, $key);
            if ($value !== null && trim((string) $value) !== '') {
                return $value;
            }

            if (! str_contains($key, '.')) {
                $nestedValue = data_get($data, 'data.'.$key);
                if ($nestedValue !== null && trim((string) $nestedValue) !== '') {
                    return $nestedValue;
                }
            }
        }

        return $default;
    }

    private function isValidRuc(string $ruc): bool
    {
        if (! preg_match('/^\d{11}$/', $ruc)) {
            return false;
        }

        $weights = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += ((int) $ruc[$i]) * $weights[$i];
        }

        $digit = 11 - ($sum % 11);
        if ($digit === 10) {
            $digit = 0;
        } elseif ($digit === 11) {
            $digit = 1;
        }

        return $digit === (int) $ruc[10];
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

        $message = trim((string) ($message ?? ''));
        $genericMessage = preg_replace('/[^a-z]/', '', strtolower($message));
        if ($message === '' || in_array($genericMessage, ['ocurriunerror', 'ocurriounerror'], true)) {
            return $fallback;
        }

        return $message;
    }
}
