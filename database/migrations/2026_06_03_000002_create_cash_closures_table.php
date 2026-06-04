<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_closures', function (Blueprint $table): void {
            $table->id();
            $table->date('business_date')->unique();
            $table->unsignedInteger('orders_count')->default(0);
            $table->decimal('gross_sales', 10, 2)->default(0);
            $table->decimal('verified_sales', 10, 2)->default(0);
            $table->decimal('cash_sales', 10, 2)->default(0);
            $table->decimal('digital_sales', 10, 2)->default(0);
            $table->decimal('declared_cash', 10, 2)->default(0);
            $table->decimal('expected_cash', 10, 2)->default(0);
            $table->decimal('difference_amount', 10, 2)->default(0);
            $table->string('notes', 500)->nullable();
            $table->json('summary_payload')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_closures');
    }
};
