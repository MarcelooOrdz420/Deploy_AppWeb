<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_order_drafts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_session', 120)->nullable();
            $table->string('email', 120)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('delivery_address', 500)->nullable();
            $table->string('delivery_reference', 300)->nullable();
            $table->json('items')->nullable();
            $table->json('metadata')->nullable();
            $table->string('status', 30)->default('active');
            $table->dateTime('last_message_at')->nullable();
            $table->timestamps();

            $table->index(['guest_session', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('last_message_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_order_drafts');
    }
};
