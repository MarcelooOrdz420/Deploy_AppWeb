<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_profiles', function (Blueprint $table): void {
            $table->id();
            $table->string('location_name')->nullable();
            $table->string('address')->nullable();
            $table->string('reference')->nullable();
            $table->text('google_maps_url')->nullable();
            $table->text('google_maps_embed_url')->nullable();
            $table->string('business_hours')->nullable();
            $table->string('service_modes')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->text('pickup_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_profiles');
    }
};
