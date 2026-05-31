<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $origin = (string) $request->headers->get('Origin', '');
        $allowedOrigin = $this->allowedOrigin($origin);

        if ($request->getMethod() === 'OPTIONS') {
            return response('', 204)->withHeaders($this->headers($allowedOrigin));
        }

        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        foreach ($this->headers($allowedOrigin) as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }

    private function headers(?string $allowedOrigin): array
    {
        $headers = [
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Accept, Authorization, X-Requested-With',
            'Access-Control-Max-Age' => '600',
        ];

        if ($allowedOrigin !== null) {
            $headers['Access-Control-Allow-Origin'] = $allowedOrigin;
            $headers['Access-Control-Allow-Credentials'] = 'true';
            $headers['Vary'] = 'Origin';
        }

        return $headers;
    }

    private function allowedOrigin(string $origin): ?string
    {
        $origin = rtrim(trim($origin), '/');
        if ($origin === '') return null;

        $raw = (string) env('CORS_ALLOWED_ORIGINS', '');
        $raw = trim($raw);

        if ($raw === '' || $raw === '*') return $origin;

        $allowed = array_filter(array_map(static fn ($value) => rtrim(trim($value), '/'), explode(',', $raw)));

        foreach ($allowed as $pattern) {
            if ($pattern === $origin) return $origin;
            if (str_contains($pattern, '*') && $this->wildcardMatch($origin, $pattern)) return $origin;
        }

        return null;
    }

    private function wildcardMatch(string $value, string $pattern): bool
    {
        $quoted = preg_quote($pattern, '/');
        $regex = '/^'.str_replace('\\*', '.*', $quoted).'$/i';
        return (bool) preg_match($regex, $value);
    }
}
