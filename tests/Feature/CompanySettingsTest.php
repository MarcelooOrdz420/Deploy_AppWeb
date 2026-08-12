<?php

namespace Tests\Feature;

use App\Services\CompanySettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class CompanySettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_settings_include_cash_on_delivery(): void
    {
        $payment = app(CompanySettingsService::class)->publicSettings()['payments']['cod'];

        $this->assertTrue($payment['enabled']);
        $this->assertSame('Pago contraentrega', $payment['label']);
    }

    public function test_saved_location_values_are_returned_instead_of_environment_defaults(): void
    {
        config([
            'company.location.address' => 'Direccion del entorno',
            'company.location.reference' => 'Referencia del entorno',
        ]);

        $service = app(CompanySettingsService::class);
        $profile = $service->updateLocationSettings([
            'address' => 'Av. Nueva 456',
            'reference' => 'Frente al parque',
        ]);

        $settings = $service->locationSettings($profile);
        $this->assertSame('Av. Nueva 456', $settings['address']);
        $this->assertSame('Frente al parque', $settings['reference']);
    }

    public function test_intentionally_empty_values_do_not_revert_to_environment_defaults(): void
    {
        config(['company.location.reference' => 'Referencia del entorno']);

        $service = app(CompanySettingsService::class);
        $profile = $service->updateLocationSettings(['reference' => '']);

        $this->assertSame('', $service->locationSettings($profile)['reference']);
    }

    public function test_update_fails_clearly_when_company_profiles_migration_is_missing(): void
    {
        Schema::drop('company_profiles');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('company_profiles no existe');

        app(CompanySettingsService::class)->updateLocationSettings(['address' => 'No persistir']);
    }
}
