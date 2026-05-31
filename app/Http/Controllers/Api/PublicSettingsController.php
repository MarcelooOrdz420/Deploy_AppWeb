<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CompanySettingsService;
use Illuminate\Http\JsonResponse;

class PublicSettingsController extends Controller
{
    public function __invoke(CompanySettingsService $companySettingsService): JsonResponse
    {
        return response()->json($companySettingsService->publicSettings());
    }
}
