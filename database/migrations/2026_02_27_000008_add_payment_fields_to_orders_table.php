<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('payment_method', 20)->default('cod')->after('total_amount');
            $table->string('payment_reference', 120)->nullable()->after('payment_method');
            $table->string('payment_status', 20)->default('pending')->after('payment_reference');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['payment_method', 'payment_reference', 'payment_status']);
        });
    }
};
