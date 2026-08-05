<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('marketing_offers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120);
            $table->string('message', 255);
            $table->string('body', 255)->nullable();
            $table->string('image_url', 2048)->nullable();
            $table->decimal('original_price', 10, 2);
            $table->decimal('promo_price', 10, 2);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->foreignId('marketing_offer_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
            $table->decimal('original_unit_price', 10, 2)->nullable()->after('unit_price');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('original_unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('marketing_offer_id');
            $table->dropColumn(['original_unit_price', 'discount_amount']);
        });
        Schema::dropIfExists('marketing_offers');
    }
};
