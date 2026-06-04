<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashClosure extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_date',
        'orders_count',
        'gross_sales',
        'verified_sales',
        'cash_sales',
        'digital_sales',
        'declared_cash',
        'expected_cash',
        'difference_amount',
        'notes',
        'summary_payload',
        'closed_by',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'business_date' => 'date',
            'gross_sales' => 'decimal:2',
            'verified_sales' => 'decimal:2',
            'cash_sales' => 'decimal:2',
            'digital_sales' => 'decimal:2',
            'declared_cash' => 'decimal:2',
            'expected_cash' => 'decimal:2',
            'difference_amount' => 'decimal:2',
            'summary_payload' => 'array',
            'closed_at' => 'datetime',
        ];
    }

    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
