<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $fillable = [
        'location_name',
        'address',
        'reference',
        'google_maps_url',
        'google_maps_embed_url',
        'business_hours',
        'service_modes',
        'delivery_notes',
        'pickup_notes',
    ];
}
