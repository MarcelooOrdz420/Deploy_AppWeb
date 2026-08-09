<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CompanySettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class AdminCompanyProfileController extends Controller
{
    public function show(CompanySettingsService $companySettingsService): JsonResponse
    {
        return response()->json([
            'location' => $companySettingsService->locationSettings(),
        ]);
    }

    public function update(Request $request, CompanySettingsService $companySettingsService): JsonResponse
    {
        $data = $request->validate([
            'location_name' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:220'],
            'reference' => ['nullable', 'string', 'max:220'],
            'google_maps_url' => ['nullable', 'url', 'max:1000'],
            'google_maps_embed_url' => ['nullable', 'url', 'max:1000'],
            'business_hours' => ['nullable', 'string', 'max:180'],
            'service_modes' => ['nullable', 'string', 'max:180'],
            'delivery_notes' => ['nullable', 'string', 'max:1000'],
            'pickup_notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $data = array_map(static fn (mixed $value): mixed => $value ?? '', $data);

        try {
            $profile = $companySettingsService->updateLocationSettings($data);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'requires_migration' => true,
                'migration' => '2026_06_12_000002_create_company_profiles_table',
                'command' => 'php artisan migrate --force',
            ], 409);
        }

        return response()->json([
            'message' => 'Configuracion de ubicacion guardada correctamente.',
            'location' => $companySettingsService->locationSettings($profile),
        ]);
    }
}
