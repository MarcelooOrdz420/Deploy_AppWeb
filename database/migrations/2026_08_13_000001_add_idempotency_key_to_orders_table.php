<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('idempotency_key', 80)->nullable()->after('user_id');
            $table->string('checkout_fingerprint', 64)->nullable()->after('idempotency_key');
            $table->unique(['user_id', 'idempotency_key'], 'orders_user_idempotency_unique');
            $table->index(['user_id', 'checkout_fingerprint', 'created_at'], 'orders_checkout_fingerprint_index');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique('orders_user_idempotency_unique');
            $table->dropIndex('orders_checkout_fingerprint_index');
            $table->dropColumn(['idempotency_key', 'checkout_fingerprint']);
        });
    }
};
