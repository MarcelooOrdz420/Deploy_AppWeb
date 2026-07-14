<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'order_id', 'provider', 'merchant_order_id', 'transaction_uuid', 'status',
        'amount', 'currency', 'authorization_number', 'response_code',
        'response_message', 'form_token_reference', 'raw_response', 'processed_at',
    ];

    protected $hidden = ['form_token_reference', 'raw_response'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'form_token_reference' => 'encrypted',
            'raw_response' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
