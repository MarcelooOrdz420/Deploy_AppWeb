<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PREPARING = 'preparing';
    public const STATUS_ON_THE_WAY = 'on_the_way';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'tracking_code',
        'customer_name',
        'customer_phone',
        'customer_email',
        'delivery_type',
        'scheduled_for',
        'delivery_window_label',
        'status',
        'total_amount',
        'payment_method',
        'payment_gateway',
        'payment_reference',
        'payment_proof_path',
        'payment_status',
        'payment_reported_at',
        'payment_verified_at',
        'billing_document_type',
        'billing_document_number',
        'billing_name',
        'billing_email',
        'billing_address',
        'billing_receipt_type',
        'billing_metadata',
        'salad_type',
        'drink_note',
        'address',
        'reference',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'scheduled_for' => 'datetime',
            'payment_reported_at' => 'datetime',
            'payment_verified_at' => 'datetime',
            'billing_metadata' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }
}
