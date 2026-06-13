<?php

namespace App\Services;

use App\Models\CompanyProfile;
use Illuminate\Support\Facades\Schema;

class CompanySettingsService
{
    public function publicSettings(): array
    {
        return [
            'brand_name' => config('company.brand_name'),
            'legal_name' => config('company.legal_name'),
            'ruc' => config('company.ruc'),
            'support_phone' => config('company.support_phone'),
            'support_email' => config('company.support_email'),
            'currency' => config('company.currency', 'PEN'),
            'location' => $this->locationSettings(),
            'payments' => [
                'yape' => [
                    'label' => config('company.payments.yape.label'),
                    'phone' => config('company.payments.yape.phone'),
                    'qr_url' => $this->assetUrl(config('company.payments.yape.qr_path')),
                    'enabled' => (bool) config('company.payments.yape.enabled'),
                ],
                'plin' => [
                    'label' => config('company.payments.plin.label'),
                    'phone' => config('company.payments.plin.phone'),
                    'qr_url' => $this->assetUrl(config('company.payments.plin.qr_path')),
                    'enabled' => (bool) config('company.payments.plin.enabled'),
                ],
                'cod' => [
                    'label' => config('company.payments.cod.label'),
                    'message' => config('company.payments.cod.message'),
                    'enabled' => (bool) config('company.payments.cod.enabled'),
                ],
                'mercado_pago' => [
                    'label' => config('company.payments.mercado_pago.label'),
                    'enabled' => (bool) config('company.payments.mercado_pago.enabled'),
                    'public_key' => config('services.mercadopago.public_key'),
                ],
            ],
        ];
    }

    public function locationSettings(?CompanyProfile $profile = null): array
    {
        $profile ??= $this->profile();

        return [
            'location_name' => $profile?->location_name ?: config('company.location.location_name', 'Local principal'),
            'address' => $profile?->address ?: config('company.location.address', 'Jr. Cuzco, Huancayo, Peru'),
            'reference' => $profile?->reference ?: config('company.location.reference', 'Zona comercial cercana a Rock and Pop'),
            'google_maps_url' => $profile?->google_maps_url ?: config('company.location.google_maps_url', 'https://maps.google.com/?q=Jr.%20Cuzco%20Huancayo%20Peru'),
            'google_maps_embed_url' => $profile?->google_maps_embed_url ?: config('company.location.google_maps_embed_url', 'https://maps.google.com/maps?q=Jr.%20Cuzco%20Huancayo%20Peru&t=&z=16&ie=UTF8&iwloc=&output=embed'),
            'business_hours' => $profile?->business_hours ?: config('company.location.business_hours', 'Atencion continua hasta las 11:00 PM'),
            'service_modes' => $profile?->service_modes ?: config('company.location.service_modes', 'Atencion en local, recojo y delivery'),
            'delivery_notes' => $profile?->delivery_notes ?: config('company.location.delivery_notes', 'Envia una referencia visible como color de puerta, piso o negocio cercano.'),
            'pickup_notes' => $profile?->pickup_notes ?: config('company.location.pickup_notes', 'Programa la hora si buscas evitar espera en hora pico.'),
        ];
    }

    public function updateLocationSettings(array $data): ?CompanyProfile
    {
        if (! $this->profileTableExists()) {
            return null;
        }

        $profile = CompanyProfile::query()->first() ?? new CompanyProfile();
        $profile->fill($data);
        $profile->save();

        return $profile->fresh();
    }

    private function profile(): ?CompanyProfile
    {
        if (! $this->profileTableExists()) {
            return null;
        }

        return CompanyProfile::query()->first();
    }

    private function assetUrl(?string $path): ?string
    {
        $value = trim((string) $path);

        if ($value === '') {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return rtrim((string) config('app.url'), '/').'/'.ltrim($value, '/');
    }

    private function profileTableExists(): bool
    {
        try {
            return Schema::hasTable('company_profiles');
        } catch (\Throwable) {
            return false;
        }
    }
}
