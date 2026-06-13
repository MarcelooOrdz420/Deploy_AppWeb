<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedForUser implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Order $order,
        public ?string $paymentStatus = null,
    ) {
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('user.'.$this->order->user_id);
    }

    public function broadcastAs(): string
    {
        return 'order.status.updated';
    }

    public function broadcastWith(): array
    {
        $status = (string) ($this->order->status ?? '');
        $tracking = (string) ($this->order->tracking_code ?? '');

        return [
            'type' => 'order_status_updated',
            'target' => 'customer',
            'title' => 'Actualizacion de pedido',
            'message' => $tracking !== '' ? "Pedido {$tracking}: {$status}" : "Pedido actualizado: {$status}",
            'body' => $tracking !== '' ? "Tu pedido {$tracking} cambio a {$status}." : "Tu pedido cambio de estado.",
            'tracking_code' => $tracking,
            'status' => $status,
            'payment_status' => $this->paymentStatus ?? (string) ($this->order->payment_status ?? ''),
            'payment_method' => (string) ($this->order->payment_method ?? ''),
            'route' => '/mis-pedidos',
            'order_id' => $this->order->id,
            'created_at' => optional($this->order->updated_at)?->toIso8601String(),
        ];
    }
}
