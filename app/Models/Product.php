<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $appends = [
        'is_sold_out',
        'can_sell',
        'availability_label',
    ];

    protected $fillable = [
        'name',
        'category',
        'description',
        'price',
        'image_url',
        'is_available',
        'stock',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
            'stock' => 'integer',
        ];
    }

    public function getIsSoldOutAttribute(): bool
    {
        return (int) ($this->stock ?? 0) <= 0;
    }

    public function getCanSellAttribute(): bool
    {
        return (bool) $this->is_available && ! $this->is_sold_out;
    }

    public function getAvailabilityLabelAttribute(): string
    {
        if (! $this->is_available) {
            return 'Inactivo';
        }

        if ($this->is_sold_out) {
            return 'Agotado';
        }

        return 'Activo';
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
