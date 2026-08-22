<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeliveryMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user || $user->role !== 'delivery') {
            return response()->json(['message' => 'Acceso solo para repartidores.'], 403);
        }

        return $next($request);
    }
}
