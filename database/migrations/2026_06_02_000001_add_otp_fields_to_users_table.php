<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_verified')->default(false)->after('is_active');
            $table->string('otp_code', 255)->nullable()->after('email_verified_at');
            $table->dateTime('otp_expires_at')->nullable()->after('otp_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['is_verified', 'otp_code', 'otp_expires_at']);
        });
    }
};
