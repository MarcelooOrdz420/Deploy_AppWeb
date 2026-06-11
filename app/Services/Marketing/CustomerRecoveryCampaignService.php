<?php

namespace App\Services\Marketing;

use App\Models\CartRecovery;
use App\Models\LoginHistory;
use App\Models\User;
use App\Services\Mail\CustomerLifecycleEmailService;
use Illuminate\Support\Carbon;

class CustomerRecoveryCampaignService
{
    public function __construct(
        private readonly CustomerLifecycleEmailService $customerLifecycleEmailService,
    ) {
    }

    public function sendInactiveUserEmails(int $days = 7): array
    {
        $threshold = now()->subDays(max(1, $days));
        $sent = 0;
        $skipped = 0;
        $failed = 0;

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
            ->chunkById(100, function ($users) use (&$sent, &$skipped, &$failed, $threshold): void {
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
                        $sent++;
                    } catch (\Throwable) {
                        $failed++;
                    }
                }
            });

        return compact('sent', 'skipped', 'failed');
    }

    public function sendAbandonedCartEmails(int $hours = 3): array
    {
        $threshold = now()->subHours(max(1, $hours));
        $cooldown = now()->subDay();
        $sent = 0;
        $skipped = 0;
        $failed = 0;

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
            ->chunkById(100, function ($carts) use (&$sent, &$skipped, &$failed): void {
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
                        $sent++;
                    } catch (\Throwable) {
                        $failed++;
                    }
                }
            });

        return compact('sent', 'skipped', 'failed');
    }
}
