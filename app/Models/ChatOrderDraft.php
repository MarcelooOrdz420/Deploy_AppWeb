<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatOrderDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guest_session',
        'email',
        'phone',
        'customer_name',
        'delivery_type',
        'delivery_address',
        'delivery_reference',
        'payment_method',
        'payment_reference',
        'salad_type',
        'billing_receipt_type',
        'billing_document_type',
        'billing_document_number',
        'billing_name',
        'items',
        'metadata',
        'status',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'metadata' => 'array',
            'last_message_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
