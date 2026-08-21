<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user || ! in_array($user->role, ['admin', 'reviewer'], true)) {
            return response()->json(['message' => 'Acceso solo para administradores.'], 403);
        }

        if ($user->role === 'reviewer' && ! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return response()->json(['message' => 'Cuenta de solo revision. No puedes realizar cambios.'], 403);
        }

        return $next($request);
    }
}
