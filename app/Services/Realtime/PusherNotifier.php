<?php

namespace App\Services\Realtime;

use Pusher\Pusher;

class PusherNotifier
{
    public function isConfigured(): bool
    {
        return $this->value('app_id') !== ''
            && $this->value('key') !== ''
            && $this->value('secret') !== '';
    }

    public function trigger(string $channel, string $event, array $payload): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $this->client()->trigger($channel, $event, $payload);

        return true;
    }

    private function client(): Pusher
    {
        return new Pusher(
            $this->value('key'),
            $this->value('secret'),
            $this->value('app_id'),
            config('broadcasting.connections.pusher.options', []),
        );
    }

    private function value(string $key): string
    {
        return trim((string) config("broadcasting.connections.pusher.{$key}", ''));
    }
}
