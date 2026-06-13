<?php

namespace App\Services\Marketing;

use App\Models\CartRecovery;
use App\Models\LoginHistory;
use App\Models\User;
use App\Services\Fcm\FcmClient;
use App\Services\Mail\CustomerLifecycleEmailService;
use Illuminate\Support\Carbon;

class CustomerRecoveryCampaignService
{
    public function __construct(
        private readonly CustomerLifecycleEmailService $customerLifecycleEmailService,
    ) {
    }

    public function sendInactiveUserEmails(int $days = 5, bool $sendPush = true): array
    {
        $threshold = now()->subDays(max(1, $days));
        $sent = 0;
        $skipped = 0;
        $failed = 0;
        $pushSent = 0;

        User::query()
            ->where('is_active', true)
            ->where('is_verified', true)
            ->where('marketing_emails_enabled', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->where(function ($query) use ($threshold): void {
                $query->whereNull('last_reengagement_email_sent_at')
                    ->orWhere('last_reengagement_email_sent_at', '<=', $threshold);
            })
            ->addSelect([
                'last_successful_login_at' => LoginHistory::query()
                    ->select('created_at')
                    ->whereColumn('user_id', 'users.id')
                    ->where('successful', true)
                    ->latest('created_at')
                    ->limit(1),
            ])
            ->orderBy('id')
            ->chunkById(100, function ($users) use (&$sent, &$skipped, &$failed, &$pushSent, $threshold, $sendPush): void {
                foreach ($users as $user) {
                    $lastSeenAt = $user->last_successful_login_at
                        ? Carbon::parse($user->last_successful_login_at)
                        : $user->created_at;

                    if (! $lastSeenAt || $lastSeenAt->gt($threshold)) {
                        $skipped++;
                        continue;
                    }

                    try {
                        $this->customerLifecycleEmailService->sendInactiveRecovery($user, $lastSeenAt);
                        $user->forceFill([
                            'last_reengagement_email_sent_at' => now(),
                        ])->save();
                        if ($sendPush && $this->sendPushToUser(
                            userId: (int) $user->id,
                            title: 'Te extranan en El Dorado',
                            body: 'Tenemos promos nuevas y tus favoritos siguen esperandote.',
                        )) {
                            $pushSent++;
                        }
                        $sent++;
                    } catch (\Throwable) {
                        $failed++;
                    }
                }
            });

        return compact('sent', 'skipped', 'failed', 'pushSent');
    }

    public function sendAbandonedCartEmails(int $hours = 3, bool $sendPush = true): array
    {
        $threshold = now()->subHours(max(1, $hours));
        $cooldown = now()->subDay();
        $sent = 0;
        $skipped = 0;
        $failed = 0;
        $pushSent = 0;

        CartRecovery::query()
            ->with('user')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->whereNull('converted_at')
            ->where('last_synced_at', '<=', $threshold)
            ->whereHas('user', fn ($query) => $query->where('marketing_emails_enabled', true))
            ->where(function ($query) use ($cooldown): void {
                $query->whereNull('abandoned_email_sent_at')
                    ->orWhere('abandoned_email_sent_at', '<=', $cooldown);
            })
            ->orderBy('id')
            ->chunkById(100, function ($carts) use (&$sent, &$skipped, &$failed, &$pushSent, $sendPush): void {
                foreach ($carts as $cart) {
                    if ((int) $cart->items_count <= 0) {
                        $skipped++;
                        continue;
                    }

                    try {
                        $this->customerLifecycleEmailService->sendAbandonedCart($cart);
                        $cart->forceFill([
                            'abandoned_email_sent_at' => now(),
                        ])->save();
                        if ($sendPush && $this->sendPushToUser(
                            userId: (int) $cart->user_id,
                            title: 'Tu carrito te espera',
                            body: 'Vuelve a tu carrito y termina tu compra antes de que se te antoje otra vez.',
                        )) {
                            $pushSent++;
                        }
                        $sent++;
                    } catch (\Throwable) {
                        $failed++;
                    }
                }
            });

        return compact('sent', 'skipped', 'failed', 'pushSent');
    }

    private function sendPushToUser(int $userId, string $title, string $body): bool
    {
        try {
            /** @var FcmClient $client */
            $client = app(FcmClient::class);
            if (! $client->isConfigured() || $userId <= 0) {
                return false;
            }

            $client->sendToTopic(
                topic: "orders_user_{$userId}",
                notification: [
                    'title' => $title,
                    'body' => $body,
                ],
                data: [
                    'route' => '/promo',
                    'type' => 'marketing_reengagement',
                ],
            );

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
