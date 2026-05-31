<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfferNotificationSent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $target = 'all',
        public string $title,
        public string $message,
        public ?string $body = null,
        public ?string $imageUrl = null,
        public ?string $ctaLabel = null,
    ) {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('mi-canal');
    }

    public function broadcastAs(): string
    {
        return 'mi-evento';
    }

    public function broadcastWith(): array
    {
        return [
            'target' => $this->target,
            'title' => $this->title,
            'message' => $this->message,
            'body' => $this->body ?? $this->message,
            'image_url' => $this->imageUrl,
            'cta_label' => $this->ctaLabel,
        ];
    }
}
