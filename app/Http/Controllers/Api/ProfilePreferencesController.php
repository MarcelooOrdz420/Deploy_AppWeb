<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ProfilePreferencesController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        if (! Schema::hasColumn('users', 'marketing_emails_enabled')) {
            return response()->json([
                'marketing_emails_enabled' => true,
                'message' => 'Preferencias disponibles despues de actualizar la base de datos.',
            ]);
        }

        return response()->json([
            'marketing_emails_enabled' => (bool) $request->user()->marketing_emails_enabled,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        if (! Schema::hasColumn('users', 'marketing_emails_enabled')) {
            return response()->json([
                'message' => 'Falta actualizar la base de datos para guardar esta preferencia.',
                'preferences' => [
                    'marketing_emails_enabled' => true,
                ],
            ], 409);
        }

        $data = $request->validate([
            'marketing_emails_enabled' => ['required', 'boolean'],
        ]);

        $user = $request->user();
        $user->forceFill([
            'marketing_emails_enabled' => (bool) $data['marketing_emails_enabled'],
        ])->save();

        return response()->json([
            'message' => 'Preferencias de correo actualizadas.',
            'preferences' => [
                'marketing_emails_enabled' => (bool) $user->marketing_emails_enabled,
            ],
        ]);
    }
}
