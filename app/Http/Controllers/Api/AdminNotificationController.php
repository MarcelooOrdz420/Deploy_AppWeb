<?php

namespace App\Http\Controllers\Api;

use App\Events\OfferNotificationSent;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\MarketingOffer;
use App\Services\Fcm\FcmClient;
use App\Services\Marketing\CustomerRecoveryCampaignService;
use App\Services\Mail\CustomerLifecycleEmailService;
use App\Services\PromotionImageService;
use App\Services\Realtime\PusherNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use ReflectionClass;
use Throwable;

class AdminNotificationController extends Controller
{
    public function sendOffer(Request $request, PromotionImageService $imageService): JsonResponse
    {
        $data = $request->validate([
            'target' => ['nullable', 'string', 'in:mobile,web,all'],
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
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('offers/admin', 'public');
            $data['image_url'] = Storage::url($path);
        }

        $product = Product::query()->findOrFail($data['product_id']);
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
                    'message' => "El {$data['discount_percent']}% da S/ ".number_format($calculatedPrice, 2).". Solo puedes ajustar hasta S/ ".number_format($allowedAdjustment, 2).' para evitar una promoción engañosa.',
                ], 422);
            }
        }
        if ($promoPrice >= $normalPrice) {
            return response()->json(['message' => 'El precio promocional debe ser menor que el precio normal del platillo.'], 422);
        }
        $discountPercent = round((1 - ($promoPrice / $normalPrice)) * 100, 2);
        $offer = MarketingOffer::create([
            'product_id' => $product->id,
            'title' => $data['title'],
            'message' => $data['message'],
            'body' => $data['body'] ?? null,
            'image_url' => $data['image_url'] ?? $product->image_url,
            'original_price' => $normalPrice,
            'promo_price' => $promoPrice,
            'discount_percent' => $discountPercent,
            'is_active' => true,
        ]);

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
        $data['cta_url'] = $broadcastPayload['cta_url'];

        $broadcast = $this->broadcastOffer($broadcastPayload);

        $push = null;
        $email = null;
        $sendPush = (bool) ($data['send_push'] ?? false);
        $sendEmail = (bool) ($data['send_email'] ?? false);

        if ($sendPush) {
            try {
                $target = (string) ($data['target'] ?? 'all');
                $topic = $target === 'mobile' ? 'promo_mobile' : 'promo_all';

                if ($target === 'web') {
                    $push = ['ok' => true, 'message' => 'Canal web emitido correctamente por Pusher.'];
                } else {
                    $client = app(FcmClient::class);
                    $client->sendToTopic(
                        topic: $topic,
                        notification: [
                            'title' => $data['title'],
                            'body' => $data['message'],
                            'image' => $data['image_url'] ?? null,
                        ],
                        data: [
                            'route' => '/promo',
                            'target' => $target,
                            'title' => $data['title'],
                            'message' => $data['message'],
                            'body' => $data['body'] ?? $data['message'],
                            'image_url' => $data['image_url'] ?? null,
                            'cta_label' => $data['cta_label'] ?? null,
                            'product_id' => isset($data['product_id']) ? (string) $data['product_id'] : '',
                            'cta_url' => $broadcastPayload['cta_url'],
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
            'payload' => $broadcastPayload + [
                'send_push' => $sendPush,
                'send_email' => $sendEmail,
                'email_subject' => $data['email_subject'] ?? null,
            ],
        ]);
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
