<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfilePreferencesController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'marketing_emails_enabled' => (bool) $request->user()->marketing_emails_enabled,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
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
