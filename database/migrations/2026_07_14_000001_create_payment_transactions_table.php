<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 30)->default('izipay')->index();
            $table->string('merchant_order_id', 120)->index();
            $table->string('transaction_uuid', 120)->nullable()->unique();
            $table->string('status', 20)->default('pending')->index();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);
            $table->string('authorization_number', 120)->nullable();
            $table->string('response_code', 80)->nullable();
            $table->string('response_message', 500)->nullable();
            $table->text('form_token_reference')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->index(['order_id', 'provider', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
