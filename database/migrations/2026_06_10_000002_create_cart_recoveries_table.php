<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_recoveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('email', 120);
            $table->string('customer_name', 120)->nullable();
            $table->string('source', 30)->default('web');
            $table->json('items');
            $table->unsignedInteger('items_count')->default(0);
            $table->decimal('subtotal_amount', 10, 2)->default(0);
            $table->dateTime('last_synced_at');
            $table->dateTime('abandoned_email_sent_at')->nullable();
            $table->dateTime('converted_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->index('last_synced_at');
            $table->index('abandoned_email_sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_recoveries');
    }
};
