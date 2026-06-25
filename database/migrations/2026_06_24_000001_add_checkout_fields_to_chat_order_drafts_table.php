<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_order_drafts', function (Blueprint $table): void {
            $table->string('customer_name', 120)->nullable()->after('phone');
            $table->string('delivery_type', 30)->nullable()->after('customer_name');
            $table->string('payment_method', 40)->nullable()->after('delivery_reference');
            $table->string('payment_reference', 120)->nullable()->after('payment_method');
            $table->string('salad_type', 30)->nullable()->after('payment_reference');
            $table->string('billing_receipt_type', 30)->nullable()->after('salad_type');
            $table->string('billing_document_type', 30)->nullable()->after('billing_receipt_type');
            $table->string('billing_document_number', 20)->nullable()->after('billing_document_type');
            $table->string('billing_name', 180)->nullable()->after('billing_document_number');
        });
    }

    public function down(): void
    {
        Schema::table('chat_order_drafts', function (Blueprint $table): void {
            $table->dropColumn([
                'customer_name',
                'delivery_type',
                'payment_method',
                'payment_reference',
                'salad_type',
                'billing_receipt_type',
                'billing_document_type',
                'billing_document_number',
                'billing_name',
            ]);
        });
    }
};
