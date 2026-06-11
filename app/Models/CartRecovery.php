<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartRecovery extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'customer_name',
        'source',
        'items',
        'items_count',
        'subtotal_amount',
        'last_synced_at',
        'abandoned_email_sent_at',
        'converted_at',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'subtotal_amount' => 'decimal:2',
            'last_synced_at' => 'datetime',
            'abandoned_email_sent_at' => 'datetime',
            'converted_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
