<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('customer_email', 120)->nullable()->after('customer_phone');
            $table->string('billing_document_type', 20)->nullable()->after('payment_status');
            $table->string('billing_document_number', 20)->nullable()->after('billing_document_type');
            $table->string('billing_name', 180)->nullable()->after('billing_document_number');
            $table->string('billing_email', 120)->nullable()->after('billing_name');
            $table->string('billing_address', 255)->nullable()->after('billing_email');
            $table->string('billing_receipt_type', 20)->nullable()->after('billing_address');
            $table->json('billing_metadata')->nullable()->after('billing_receipt_type');
            $table->string('payment_gateway', 30)->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn([
                'customer_email',
                'billing_document_type',
                'billing_document_number',
                'billing_name',
                'billing_email',
                'billing_address',
                'billing_receipt_type',
                'billing_metadata',
                'payment_gateway',
            ]);
        });
    }
};
