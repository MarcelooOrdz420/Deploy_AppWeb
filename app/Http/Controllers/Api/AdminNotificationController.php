<?php

namespace App\Http\Controllers\Api;

use App\Events\OfferNotificationSent;
use App\Http\Controllers\Controller;
use App\Models\MarketingOffer;
use App\Models\Product;
use App\Models\User;
use App\Services\Fcm\FcmClient;
use App\Services\Mail\CustomerLifecycleEmailService;
use App\Services\Marketing\CustomerRecoveryCampaignService;
use App\Services\PromotionImageService;
use App\Services\Realtime\PusherNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use Throwable;

class AdminNotificationController extends Controller
{
    public function sendOffer(Request $request, PromotionImageService $imageService): JsonResponse
    {
        $data = $request->validate([
            'target' => ['nullable', 'string', 'in:mobile,web,all'],
            'send_realtime' => ['nullable', 'boolean'],
            'send_push' => ['nullable', 'boolean'],
            'send_email' => ['nullable', 'boolean'],
            'email_subject' => ['nullable', 'string', 'max:140'],
            'title' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'image' => ['nullable', 'file', 'image', 'max:5120'],
            'cta_label' => ['nullable', 'string', 'max:60'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'promo_price' => ['nullable', 'numeric', 'min:0.01'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:99.99'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'duration_hours' => ['nullable', 'integer', 'min:1', 'max:720'],
            'push_target' => ['nullable', 'string', 'in:home,product'],
            'online_only' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('offers/admin', 'public');
            // Keep local uploads on the current origin. This avoids mixed-content
            // failures and stale domains when APP_URL changes between deployments.
            $data['image_url'] = '/media/promotions/'.ltrim($path, '/');
        }

        $product = Product::query()->findOrFail($data['product_id']);

        // Un producto no puede tener dos promociones vigentes (activa o programada)
        // al mismo tiempo: si el admin vuelve a elegirlo, se bloquea aqui.
        $hasActiveOrScheduled = MarketingOffer::query()
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->exists();
        if ($hasActiveOrScheduled) {
            return response()->json([
                'message' => 'Este platillo ya tiene una promocion activa o programada. Cortala desde la lista de abajo o espera a que termine antes de crear otra.',
            ], 422);
        }

        $data['image_url'] = $imageService->resolve($data['image_url'] ?? null, $product);
        $normalPrice = round((float) $product->price, 2);
        if ($normalPrice <= 0) {
            return response()->json(['message' => 'El producto debe tener un precio normal mayor que cero para crear una promoción.'], 422);
        }
        $promoPrice = isset($data['promo_price'])
            ? round((float) $data['promo_price'], 2)
            : round($normalPrice * (1 - ((float) ($data['discount_percent'] ?? 0) / 100)), 2);
        if (isset($data['promo_price'], $data['discount_percent'])) {
            $calculatedPrice = round($normalPrice * (1 - ((float) $data['discount_percent'] / 100)), 2);
            $allowedAdjustment = max(1.00, round($normalPrice * 0.02, 2));
            if (abs($promoPrice - $calculatedPrice) > $allowedAdjustment) {
                return response()->json([
                    'message' => "El {$data['discount_percent']}% da S/ ".number_format($calculatedPrice, 2).'. Solo puedes ajustar hasta S/ '.number_format($allowedAdjustment, 2).' para evitar una promoción engañosa.',
                ], 422);
            }
        }
        if ($promoPrice >= $normalPrice) {
            return response()->json(['message' => 'El precio promocional debe ser menor que el precio normal del platillo.'], 422);
        }
        $discountPercent = round((1 - ($promoPrice / $normalPrice)) * 100, 2);
        $startsAt = isset($data['starts_at']) ? \Illuminate\Support\Carbon::parse($data['starts_at']) : now();
        $endsAt = isset($data['ends_at'])
            ? \Illuminate\Support\Carbon::parse($data['ends_at'])
            : (isset($data['duration_hours']) ? $startsAt->copy()->addHours((int) $data['duration_hours']) : null);
        // El admin decide, por cada promocion, si el precio rebajado aplica
        // solo a compras por web/app o tambien a compras presenciales. Por
        // defecto es solo web/app, que es como se venia comunicando antes.
        $onlineOnly = array_key_exists('online_only', $data) ? (bool) $data['online_only'] : true;
        $offer = MarketingOffer::create([
            'product_id' => $product->id,
            'title' => $data['title'],
            'message' => $data['message'],
            'body' => $data['body'] ?? null,
            'image_url' => $data['image_url'] ?? $product->image_url,
            'original_price' => $normalPrice,
            'promo_price' => $promoPrice,
            'discount_percent' => $discountPercent,
            'online_only' => $onlineOnly,
            'is_active' => true,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);

        if ($onlineOnly) {
            $disclaimer = 'Valido solo para compras por la web o la app. No aplica en compras presenciales.';
            $data['body'] = trim(($data['body'] ?? $data['message']).' '.$disclaimer);
        }

        // El banner del inicio solo tiene espacio para las primeras
        // PromotionController::BANNER_LIMIT promociones vigentes (ordenadas
        // por la que termina mas pronto). Si esta ya no entra, se avisa al
        // admin: igual se puede enviar por push/correo, solo no se vera en
        // el banner mientras las demas sigan activas.
        $activeOrScheduledCount = MarketingOffer::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->count();
        $bannerNote = $activeOrScheduledCount > PromotionController::BANNER_LIMIT
            ? 'Ya hay '.PromotionController::BANNER_LIMIT.' promociones ocupando el banner del inicio, asi que esta no se vera ahi hasta que alguna termine o la cortes manualmente. Los envios por app cerrada y correo si le llegan a todos igual.'
            : null;

        $broadcastPayload = [
            'target' => (string) ($data['target'] ?? 'all'),
            'title' => $data['title'],
            'message' => $data['message'],
            'body' => $data['body'] ?? $data['message'],
            'image_url' => $offer->image_url,
            'cta_label' => $data['cta_label'] ?? null,
            'product_id' => $data['product_id'] ?? null,
            'cta_url' => '/promociones/'.$offer->id,
            'offer_id' => $offer->id,
            'promo_price' => $offer->promo_price,
            'original_price' => $offer->original_price,
            'discount_percent' => $offer->discount_percent,
        ];
        $publicImageUrl = $offer->image_url
            ? url('/'.ltrim($offer->image_url, '/'))
            : null;
        $data['cta_url'] = $broadcastPayload['cta_url'];

        $sendRealtime = (bool) ($data['send_realtime'] ?? true);
        $broadcast = $sendRealtime
            ? $this->broadcastOffer($broadcastPayload)
            : ['ok' => true, 'skipped' => true];
        $push = null;
        $email = null;
        $sendPush = (bool) ($data['send_push'] ?? false);
        $sendEmail = (bool) ($data['send_email'] ?? false);

        if ($sendPush) {
            try {
                $target = (string) ($data['target'] ?? 'all');
                $topic = $target === 'mobile' ? 'promo_mobile' : 'promo_all';
                // 'home' abre la pantalla principal (donde ya vive la caja de
                // promocion persistente); 'product' salta directo al producto
                // o a la pagina de la promocion. Son los 2 tipos de envio que
                // el admin puede elegir por separado en el panel.
                $pushTarget = (string) ($data['push_target'] ?? 'product');

                if ($target === 'web') {
                    $push = ['ok' => true, 'message' => 'Canal web emitido correctamente por Pusher.'];
                } else {
                    $client = app(FcmClient::class);
                    $client->sendToTopic(
                        topic: $topic,
                        notification: [
                            'title' => $data['title'],
                            'body' => $data['message'],
                            'image' => $publicImageUrl,
                        ],
                        data: [
                            'route' => $pushTarget === 'home' ? '/app' : '/promo',
                            'push_target' => $pushTarget,
                            'target' => $target,
                            'title' => $data['title'],
                            'message' => $data['message'],
                            'body' => $data['body'] ?? $data['message'],
                            'image_url' => $publicImageUrl,
                            'cta_label' => $data['cta_label'] ?? null,
                            'product_id' => isset($data['product_id']) ? (string) $data['product_id'] : '',
                            'cta_url' => $broadcastPayload['cta_url'],
                            'offer_id' => (string) $offer->id,
                            'promo_price' => (string) $offer->promo_price,
                            'original_price' => (string) $offer->original_price,
                            'discount_percent' => (string) $offer->discount_percent,
                        ],
                    );

                    $push = ['ok' => true, 'topic' => $topic];
                }
            } catch (Throwable $e) {
                $push = ['ok' => false, 'message' => $e->getMessage()];
            }
        }

        if ($sendEmail) {
            $email = $this->sendOfferEmailCampaign($data);
        }

        return response()->json([
            'message' => 'Notificacion enviada',
            'channel' => 'mi-canal',
            'event' => 'mi-evento',
            'broadcast' => $broadcast,
            'push' => $push,
            'email' => $email,
            'banner_note' => $bannerNote,
            'payload' => $broadcastPayload + [
                'send_push' => $sendPush,
                'send_realtime' => $sendRealtime,
                'send_email' => $sendEmail,
                'email_subject' => $data['email_subject'] ?? null,
            ],
        ]);
    }

    public function promotionsIndex(): JsonResponse
    {
        $offers = MarketingOffer::query()
            ->with('product:id,name')
            ->withCount('orderItems')
            ->orderByDesc('id')
            ->get()
            ->map(function (MarketingOffer $offer) {
                return [
                    'id' => $offer->id,
                    'title' => $offer->title,
                    'product_id' => $offer->product_id,
                    'product_name' => $offer->product?->name,
                    'original_price' => (float) $offer->original_price,
                    'promo_price' => (float) $offer->promo_price,
                    'discount_percent' => (float) $offer->discount_percent,
                    'online_only' => (bool) $offer->online_only,
                    'is_active' => (bool) $offer->is_active,
                    'title' => $offer->title,
                    'message' => $offer->message,
                    'body' => $offer->body,
                    'starts_at' => $offer->starts_at?->toIso8601String(),
                    'ends_at' => $offer->ends_at?->toIso8601String(),
                    'status' => $offer->scheduleStatus(),
                    'orders_count' => (int) $offer->order_items_count,
                    'created_at' => $offer->created_at?->toIso8601String(),
                ];
            });

        return response()->json(['data' => $offers]);
    }

    public function updatePromotion(Request $request, MarketingOffer $offer): JsonResponse
    {
        $data = $request->validate([
            'is_active' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'end_now' => ['nullable', 'boolean'],
            'duration_hours' => ['nullable', 'integer', 'min:1', 'max:720'],
            'title' => ['nullable', 'string', 'max:120'],
            'message' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:255'],
            'promo_price' => ['nullable', 'numeric', 'min:0.01'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:99.99'],
            'online_only' => ['nullable', 'boolean'],
        ]);

        $offer->loadMissing('product');
        $normalPrice = round((float) $offer->original_price, 2);

        if (isset($data['promo_price']) || isset($data['discount_percent'])) {
            $promoPrice = isset($data['promo_price'])
                ? round((float) $data['promo_price'], 2)
                : round($normalPrice * (1 - ((float) ($data['discount_percent'] ?? 0) / 100)), 2);
            if ($promoPrice >= $normalPrice) {
                return response()->json(['message' => 'El precio promocional debe ser menor que el precio normal del platillo.'], 422);
            }
            $offer->promo_price = $promoPrice;
            $offer->discount_percent = round((1 - ($promoPrice / $normalPrice)) * 100, 2);
        }

        if (array_key_exists('title', $data)) {
            $offer->title = $data['title'];
        }
        if (array_key_exists('message', $data)) {
            $offer->message = $data['message'];
        }
        if (array_key_exists('body', $data)) {
            $offer->body = $data['body'];
        }
        if (array_key_exists('online_only', $data)) {
            $offer->online_only = (bool) $data['online_only'];
        }

        if (! empty($data['end_now'])) {
            $offer->ends_at = now();
        } elseif (isset($data['duration_hours'])) {
            $offer->ends_at = now()->addHours((int) $data['duration_hours']);
        } elseif (array_key_exists('ends_at', $data)) {
            $offer->ends_at = $data['ends_at'] ? \Illuminate\Support\Carbon::parse($data['ends_at']) : null;
        }
        if (array_key_exists('starts_at', $data)) {
            $offer->starts_at = $data['starts_at'] ? \Illuminate\Support\Carbon::parse($data['starts_at']) : null;
        }

        $reactivating = array_key_exists('is_active', $data) && (bool) $data['is_active'] && ! $offer->is_active;
        if (array_key_exists('is_active', $data)) {
            $offer->is_active = (bool) $data['is_active'];
        }

        // Si se esta reactivando (o extendiendo la fecha de una vencida), no
        // debe quedar duplicada con otra promocion ya vigente del mismo
        // platillo: la misma regla que aplica al crear una promocion nueva.
        if (($reactivating || isset($data['duration_hours']) || array_key_exists('ends_at', $data)) && $offer->is_active) {
            $conflict = MarketingOffer::query()
                ->where('id', '!=', $offer->id)
                ->where('product_id', $offer->product_id)
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('ends_at')->orWhere('ends_at', '>', now());
                })
                ->exists();
            if ($conflict) {
                return response()->json([
                    'message' => 'Este platillo ya tiene otra promocion activa o programada. Cortala antes de reactivar o extender esta.',
                ], 422);
            }
        }

        $offer->save();

        return response()->json([
            'id' => $offer->id,
            'title' => $offer->title,
            'message' => $offer->message,
            'body' => $offer->body,
            'promo_price' => (float) $offer->promo_price,
            'discount_percent' => (float) $offer->discount_percent,
            'online_only' => (bool) $offer->online_only,
            'is_active' => (bool) $offer->is_active,
            'starts_at' => $offer->starts_at?->toIso8601String(),
            'ends_at' => $offer->ends_at?->toIso8601String(),
            'status' => $offer->scheduleStatus(),
        ]);
    }

    public function destroyPromotion(MarketingOffer $offer): JsonResponse
    {
        $offer->delete();

        return response()->json(['message' => 'Promocion eliminada.']);
    }

    public function sendRecoveryCampaigns(Request $request, CustomerRecoveryCampaignService $campaignService): JsonResponse
    {
        $data = $request->validate([
            'inactive_days' => ['nullable', 'integer', 'min:1', 'max:30'],
            'abandoned_hours' => ['nullable', 'integer', 'min:1', 'max:72'],
            'send_push' => ['nullable', 'boolean'],
        ]);

        $sendPush = (bool) ($data['send_push'] ?? true);
        $inactive = $campaignService->sendInactiveUserEmails(
            days: (int) ($data['inactive_days'] ?? 5),
            sendPush: $sendPush,
        );
        $abandoned = $campaignService->sendAbandonedCartEmails(
            hours: (int) ($data['abandoned_hours'] ?? 3),
            sendPush: $sendPush,
        );

        return response()->json([
            'message' => 'Campanas de recuperacion ejecutadas.',
            'inactive' => $inactive,
            'abandoned' => $abandoned,
        ]);
    }

    private function sendOfferEmailCampaign(array $data): array
    {
        $sent = 0;
        $failed = 0;

        /** @var CustomerLifecycleEmailService $emailService */
        $emailService = app(CustomerLifecycleEmailService::class);

        $query = User::query()
            ->where('is_active', true)
            ->where('is_verified', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderBy('id');

        if (Schema::hasColumn('users', 'marketing_emails_enabled')) {
            $query->where('marketing_emails_enabled', true);
        }

        $query->chunkById(100, function ($users) use (&$sent, &$failed, $emailService, $data): void {
            foreach ($users as $user) {
                try {
                    $emailService->sendPromotion($user, $data);
                    $sent++;
                } catch (Throwable) {
                    $failed++;
                }
            }
        });

        return [
            'ok' => $failed === 0,
            'sent' => $sent,
            'failed' => $failed,
        ];
    }

    private function broadcastOffer(array $payload): array
    {
        try {
            /** @var PusherNotifier $notifier */
            $notifier = app(PusherNotifier::class);
            if ($notifier->trigger('mi-canal', 'mi-evento', $payload)) {
                return ['ok' => true, 'driver' => 'pusher'];
            }
        } catch (Throwable $e) {
            return ['ok' => false, 'driver' => 'pusher', 'message' => $e->getMessage()];
        }

        try {
            $ref = new ReflectionClass(OfferNotificationSent::class);
            $ctor = $ref->getConstructor();
            $paramNames = $ctor ? array_map(fn ($p) => $p->getName(), $ctor->getParameters()) : [];

            if (in_array('target', $paramNames, true)) {
                event(new OfferNotificationSent(
                    target: (string) ($payload['target'] ?? 'all'),
                    title: $payload['title'],
                    message: $payload['message'],
                    body: $payload['body'] ?? null,
                    imageUrl: $payload['image_url'] ?? null,
                    ctaLabel: $payload['cta_label'] ?? null,
                    productId: isset($payload['product_id']) ? (int) $payload['product_id'] : null,
                    ctaUrl: $payload['cta_url'] ?? null,
                ));
            } else {
                event(new OfferNotificationSent(
                    title: $payload['title'],
                    message: $payload['message'],
                    body: $payload['body'] ?? null,
                    imageUrl: $payload['image_url'] ?? null,
                    ctaLabel: $payload['cta_label'] ?? null,
                ));
            }

            return [
                'ok' => (string) config('broadcasting.default') !== 'log',
                'driver' => (string) config('broadcasting.default'),
                'message' => (string) config('broadcasting.default') === 'log'
                    ? 'La promo fue emitida al driver log. Activa Pusher para verla en la web en tiempo real.'
                    : 'Broadcast emitido.',
            ];
        } catch (Throwable $e) {
            return ['ok' => false, 'driver' => (string) config('broadcasting.default'), 'message' => $e->getMessage()];
        }
    }
}
