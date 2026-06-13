<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dateTime('password_reset_requested_at')->nullable()->after('last_reengagement_email_sent_at');
            $table->dateTime('password_reset_completed_at')->nullable()->after('password_reset_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'password_reset_requested_at',
                'password_reset_completed_at',
            ]);
        });
    }
};
