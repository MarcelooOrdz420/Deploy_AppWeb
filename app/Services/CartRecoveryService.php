<?php

namespace App\Services;

use App\Models\CartRecovery;
use App\Models\User;

class CartRecoveryService
{
    public function syncForUser(User $user, array $items, string $source = 'web'): void
    {
        $normalizedItems = collect($items)
            ->map(function (array $item): array {
                $qty = max(0, (int) ($item['qty'] ?? 0));
                $price = round((float) ($item['price'] ?? 0), 2);

                return [
                    'id' => (int) ($item['id'] ?? 0),
                    'name' => trim((string) ($item['name'] ?? '')),
                    'category' => trim((string) ($item['category'] ?? '')),
                    'price' => $price,
                    'qty' => $qty,
                    'image_url' => trim((string) ($item['image_url'] ?? '')),
                    'line_total' => round($price * $qty, 2),
                ];
            })
            ->filter(fn (array $item): bool => $item['id'] > 0 && $item['qty'] > 0 && $item['name'] !== '')
            ->values();

        if ($normalizedItems->isEmpty()) {
            CartRecovery::query()
                ->where('user_id', $user->id)
                ->delete();

            return;
        }

        CartRecovery::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'email' => $user->email,
                'customer_name' => $user->name,
                'source' => trim($source) !== '' ? trim($source) : 'web',
                'items' => $normalizedItems->all(),
                'items_count' => $normalizedItems->sum(fn (array $item): int => (int) $item['qty']),
                'subtotal_amount' => $normalizedItems->sum(fn (array $item): float => (float) $item['line_total']),
                'last_synced_at' => now(),
                'abandoned_email_sent_at' => null,
                'converted_at' => null,
            ]
        );
    }

    public function clearForUser(User $user): void
    {
        CartRecovery::query()
            ->where('user_id', $user->id)
            ->delete();
    }
}
