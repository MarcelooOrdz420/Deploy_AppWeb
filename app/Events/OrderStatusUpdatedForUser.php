<?php

namespace App\Events;

use App\Models\Order;
use App\Models\User;
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
        public ?User $driver = null,
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
        $statusLabel = Order::statusLabel($status);
        $tracking = (string) ($this->order->tracking_code ?? '');

        $message = $tracking !== '' ? "Pedido {$tracking}: {$statusLabel}" : "Pedido actualizado: {$statusLabel}";
        $body = $tracking !== '' ? "Tu pedido {$tracking} esta {$statusLabel}." : "Tu pedido cambio de estado.";

        if ($this->driver && $status === Order::STATUS_ON_THE_WAY) {
            $body = $tracking !== ''
                ? "{$this->driver->name} ya tiene tu pedido {$tracking} y va en camino."
                : "{$this->driver->name} ya tiene tu pedido y va en camino.";
        }

        return [
            'type' => 'order_status_updated',
            'target' => 'customer',
            'title' => 'Actualizacion de pedido',
            'message' => $message,
            'body' => $body,
            'tracking_code' => $tracking,
            'status' => $status,
            'status_label' => $statusLabel,
            'payment_status' => $this->paymentStatus ?? (string) ($this->order->payment_status ?? ''),
            'payment_method' => (string) ($this->order->payment_method ?? ''),
            'route' => '/mis-pedidos',
            'order_id' => $this->order->id,
            'created_at' => optional($this->order->updated_at)?->toIso8601String(),
        ];
    }
}
