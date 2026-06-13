<?php

namespace App\Http\Controllers\Api;

use App\Events\OfferNotificationSent;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Fcm\FcmClient;
use App\Services\Marketing\CustomerRecoveryCampaignService;
use App\Services\Mail\CustomerLifecycleEmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ReflectionClass;
use Throwable;

class AdminNotificationController extends Controller
{
    public function sendOffer(Request $request): JsonResponse
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
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('offers/admin', 'public');
            $data['image_url'] = url(Storage::url($path));
        }

        $ref = new ReflectionClass(OfferNotificationSent::class);
        $ctor = $ref->getConstructor();
        $paramNames = $ctor ? array_map(fn ($p) => $p->getName(), $ctor->getParameters()) : [];

        if (in_array('target', $paramNames, true)) {
            event(new OfferNotificationSent(
                target: (string) ($data['target'] ?? 'all'),
                title: $data['title'],
                message: $data['message'],
                body: $data['body'] ?? null,
                imageUrl: $data['image_url'] ?? null,
                ctaLabel: $data['cta_label'] ?? null,
            ));
        } else {
            event(new OfferNotificationSent(
                title: $data['title'],
                message: $data['message'],
                body: $data['body'] ?? null,
                imageUrl: $data['image_url'] ?? null,
                ctaLabel: $data['cta_label'] ?? null,
            ));
        }

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
            'push' => $push,
            'email' => $email,
            'payload' => [
                'target' => (string) ($data['target'] ?? 'all'),
                'send_push' => $sendPush,
                'send_email' => $sendEmail,
                'email_subject' => $data['email_subject'] ?? null,
                'title' => $data['title'],
                'message' => $data['message'],
                'body' => $data['body'] ?? $data['message'],
                'image_url' => $data['image_url'] ?? null,
                'cta_label' => $data['cta_label'] ?? null,
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

        User::query()
            ->where('is_active', true)
            ->where('is_verified', true)
            ->where('marketing_emails_enabled', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderBy('id')
            ->chunkById(100, function ($users) use (&$sent, &$failed, $emailService, $data): void {
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
}
