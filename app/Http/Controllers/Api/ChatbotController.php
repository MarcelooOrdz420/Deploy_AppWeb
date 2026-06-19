<?php

namespace App\Http\Controllers\Api;

use App\Events\ChatbotReplySent;
use App\Models\User;
use App\Services\CartRecoveryService;
use App\Services\Chatbot\ChatOrderDraftService;
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

        $draftResult = app(ChatOrderDraftService::class)->capture(
            message: $data['message'],
            user: $user,
            guestSession: $sessionId,
        );

        if (($draftResult['order_activity'] ?? false) && ! empty($draftResult['reply'])) {
            $reply = (string) $draftResult['reply'];
        } else {
            $reply = $this->chatbot->reply(
                message: $data['message'],
                userName: $user?->name,
                sessionId: $sessionId,
                draftContext: app(ChatOrderDraftService::class)->contextFor($user, $sessionId),
            );
        }

        broadcast(new ChatbotReplySent($channel, $reply, $sessionId));

        return response()->json([
            'reply' => $reply,
            'channel' => $user ? ('private-user.'.$user->id) : $publicChannelName,
            'event' => 'chatbot.reply',
        ]);
    }

    public function status(): JsonResponse
    {
        $status = $this->chatbot->status();

        return response()->json($status, ($status['ok'] ?? false) ? 200 : 503);
    }

    public function cartIntent(Request $request, CartRecoveryService $cartRecoveryService): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:120'],
            'guest_session' => ['nullable', 'string', 'min:8', 'max:120'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer'],
            'items.*.name' => ['required', 'string', 'max:160'],
            'items.*.category' => ['nullable', 'string', 'max:80'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.image_url' => ['nullable', 'string', 'max:500'],
        ]);

        $this->tryAuthenticate($request);
        $authUser = auth()->user();
        $email = strtolower(trim((string) $data['email']));
        $registeredUser = User::query()->where('email', $email)->first();

        if ($authUser) {
            $cartRecoveryService->syncForUser(
                user: $authUser,
                items: $data['items'],
                source: 'pollia',
            );
            app(ChatOrderDraftService::class)->markConverted($authUser, $data['guest_session'] ?? null);

            return response()->json([
                'status' => 'ready_to_checkout',
                'registered' => true,
                'authenticated' => true,
                'cart_url' => '/carrito',
                'message' => 'Listo, guarde tu combinacion en tu cuenta. Puedes revisar el carrito y completar entrega, ensalada y pago.',
            ]);
        }

        if ($registeredUser) {
            return response()->json([
                'status' => 'login_required',
                'registered' => true,
                'authenticated' => false,
                'login_url' => '/login?email='.rawurlencode($email).'&next='.rawurlencode('/carrito'),
                'cart_url' => '/carrito',
                'message' => 'Ese correo ya tiene cuenta. Guarde la combinacion en este navegador; inicia sesion y luego entra al carrito para finalizar la compra.',
            ]);
        }

        return response()->json([
            'status' => 'registration_required',
            'registered' => false,
            'authenticated' => false,
            'register_url' => '/register?email='.rawurlencode($email).'&next='.rawurlencode('/carrito'),
            'cart_url' => '/carrito',
            'message' => 'No encontre una cuenta con ese correo. Guarde la combinacion en este navegador; crea tu cuenta para convertirla en pedido real.',
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
