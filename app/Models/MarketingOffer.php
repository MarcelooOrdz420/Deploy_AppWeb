<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingOffer extends Model
{
    protected $fillable = ['product_id', 'title', 'message', 'body', 'image_url', 'original_price', 'promo_price', 'discount_percent', 'is_active', 'starts_at', 'ends_at'];

    protected function casts(): array
    {
        return [
            'original_price' => 'decimal:2',
            'promo_price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function product() { return $this->belongsTo(Product::class); }
    public function orderItems() { return $this->hasMany(OrderItem::class); }

    /**
     * Activa manualmente Y dentro de su ventana de horario (hora Peru).
     * Es la unica fuente de verdad para decidir si el precio promocional
     * se muestra y se cobra ahora mismo.
     */
    public function isCurrentlyActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        $now = now();
        if ($this->starts_at && $this->starts_at->isAfter($now)) {
            return false;
        }
        if ($this->ends_at && $this->ends_at->isBefore($now)) {
            return false;
        }

        return true;
    }

    public function scheduleStatus(): string
    {
        if (! $this->is_active) {
            return 'desactivada';
        }
        $now = now();
        if ($this->starts_at && $this->starts_at->isAfter($now)) {
            return 'programada';
        }
        if ($this->ends_at && $this->ends_at->isBefore($now)) {
            return 'vencida';
        }

        return 'activa';
    }
}
