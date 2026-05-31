<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Token requerido.'], 401);
        }

        $payload = JwtService::decode($token);

        if (! $payload || ! isset($payload['sub'])) {
            return response()->json(['message' => 'Token invalido o expirado.'], 401);
        }

        $user = User::find($payload['sub']);

        if (! $user) {
            return response()->json(['message' => 'Usuario no encontrado.'], 401);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Cuenta desactivada.'], 401);
        }

        auth()->setUser($user);

        return $next($request);
    }
}
