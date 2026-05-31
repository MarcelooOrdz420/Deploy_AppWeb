<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dateTime('scheduled_for')->nullable()->after('delivery_type');
            $table->string('delivery_window_label', 120)->nullable()->after('scheduled_for');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['scheduled_for', 'delivery_window_label']);
        });
    }
};
