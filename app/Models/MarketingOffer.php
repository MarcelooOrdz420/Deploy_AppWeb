<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingOffer extends Model
{
    protected $fillable = ['product_id', 'title', 'message', 'body', 'image_url', 'original_price', 'promo_price', 'discount_percent', 'is_active'];

    protected function casts(): array
    {
        return ['original_price' => 'decimal:2', 'promo_price' => 'decimal:2', 'discount_percent' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function product() { return $this->belongsTo(Product::class); }
    public function orderItems() { return $this->hasMany(OrderItem::class); }
}
