<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PeruRegistryLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

class PeruLookupController extends Controller
{
    public function lookupDni(Request $request, PeruRegistryLookupService $service): JsonResponse
    {
        $data = $request->validate([
            'dni' => ['required', 'digits:8'],
        ]);

        try {
            return response()->json($service->lookupDni($data['dni']));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 503);
        } catch (Throwable $exception) {
            return response()->json(['message' => 'La consulta de DNI no pudo completarse en este momento.'], 503);
        }
    }
}
