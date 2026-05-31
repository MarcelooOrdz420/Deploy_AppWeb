<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pusher\Pusher;

class PusherAuthController
{
    public function __invoke(Request $request): JsonResponse
    {
        $this->tryAuthenticate($request);
        $userId = auth()->id();
        if (! $userId) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        $data = $request->validate([
            'socket_id' => ['required', 'string'],
            'channel_name' => ['required', 'string'],
        ]);

        $channelName = $data['channel_name'];
        $expected = 'private-user.'.$userId;

        if ($channelName !== $expected) {
            return response()->json(['message' => 'No autorizado para este canal.'], 403);
        }

        $pusher = new Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            config('broadcasting.connections.pusher.options'),
        );

        $auth = $pusher->authorizeChannel($channelName, $data['socket_id']);

        return response()->json(json_decode($auth, true));
    }

    private function tryAuthenticate(Request $request): void
    {
        if (auth()->check()) return;

        $token = (string) ($request->bearerToken() ?: $request->query('token', ''));
        $token = trim($token);
        if ($token === '') return;

        $payload = JwtService::decode($token);
        if (! $payload || ! isset($payload['sub'])) return;

        $user = User::find($payload['sub']);
        if (! $user || ! $user->is_active) return;

        auth()->setUser($user);
    }
}
