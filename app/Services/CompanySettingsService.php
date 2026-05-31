<?php

namespace App\Services;

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
                'transfer' => [
                    'label' => config('company.payments.transfer.label'),
                    'bank_name' => config('company.payments.transfer.bank_name'),
                    'account_number' => config('company.payments.transfer.account_number'),
                    'cci' => config('company.payments.transfer.cci'),
                    'account_holder' => config('company.payments.transfer.account_holder'),
                    'enabled' => (bool) config('company.payments.transfer.enabled'),
                ],
                'cod' => [
                    'label' => config('company.payments.cod.label'),
                    'message' => config('company.payments.cod.message'),
                    'enabled' => (bool) config('company.payments.cod.enabled'),
                ],
                'culqi' => [
                    'label' => config('company.payments.culqi.label'),
                    'enabled' => (bool) config('company.payments.culqi.enabled'),
                    'mode' => config('services.culqi.mode', 'test'),
                    'reference_only' => (bool) config('services.culqi.reference_only', true),
                    'public_key' => config('services.culqi.public_key'),
                    'rsa_id' => config('services.culqi.rsa_id'),
                    'rsa_public_key' => config('services.culqi.rsa_public_key'),
                ],
            ],
        ];
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
}
