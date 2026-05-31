<?php

namespace App\Http\Controllers\Api;

use App\Events\ChatbotReplySent;
use App\Models\User;
use App\Services\Chatbot\ChatbotService;
use App\Services\JwtService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController
{
    public function __construct(private readonly ChatbotService $chatbot)
    {
    }

    public function message(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:1200'],
            'guest_session' => ['nullable', 'string', 'min:8', 'max:120'],
        ]);

        $this->tryAuthenticate($request);
        $user = auth()->user();

        $sessionId = null;
        $channel = null;
        $publicChannelName = null;

        if ($user) {
            $channel = new PrivateChannel('user.'.$user->id);
        } else {
            $sessionId = (string) ($data['guest_session'] ?? '');
            if ($sessionId === '') {
                return response()->json(['message' => 'guest_session requerido si no hay login.'], 422);
            }
            $publicChannelName = 'chat-guest.'.$sessionId;
            $channel = new Channel($publicChannelName);
        }

        $reply = $this->chatbot->reply(
            message: $data['message'],
            userName: $user?->name,
            sessionId: $sessionId,
        );

        broadcast(new ChatbotReplySent($channel, $reply, $sessionId));

        return response()->json([
            'reply' => $reply,
            'channel' => $user ? ('private-user.'.$user->id) : $publicChannelName,
            'event' => 'chatbot.reply',
        ]);
    }

    private function tryAuthenticate(Request $request): void
    {
        if (auth()->check()) return;

        $token = $request->bearerToken();
        if (! $token) return;

        $payload = JwtService::decode($token);
        if (! $payload || ! isset($payload['sub'])) {
            throw new HttpResponseException(response()->json(['message' => 'Token invalido o expirado.'], 401));
        }

        $user = User::find($payload['sub']);
        if (! $user || ! $user->is_active) {
            throw new HttpResponseException(response()->json(['message' => 'Token invalido o expirado.'], 401));
        }

        auth()->setUser($user);
    }
}
