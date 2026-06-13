<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CompanySettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        $profile = $companySettingsService->updateLocationSettings($data);

        return response()->json([
            'message' => $profile
                ? 'Configuracion de ubicacion actualizada.'
                : 'La configuracion aun usa valores por defecto. Ejecuta la migracion pendiente para guardar cambios persistentes.',
            'location' => $companySettingsService->locationSettings($profile),
        ]);
    }
}
