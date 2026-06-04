<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreatedAlertSent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public Order $order)
    {
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
            'type' => 'order_created',
            'target' => 'admin',
            'title' => 'Nuevo pedido recibido',
            'message' => "Pedido {$this->order->tracking_code} registrado",
            'body' => trim("Cliente: {$this->order->customer_name} | Total: S/ ".number_format((float) $this->order->total_amount, 2, '.', '')." | Pago: {$this->order->payment_method}"),
            'order_id' => $this->order->id,
            'tracking_code' => $this->order->tracking_code,
            'customer_name' => $this->order->customer_name,
            'payment_method' => $this->order->payment_method,
            'payment_status' => $this->order->payment_status,
            'total_amount' => round((float) $this->order->total_amount, 2),
            'route' => '/admin/panel',
            'created_at' => optional($this->order->created_at)?->toIso8601String(),
        ];
    }
}
