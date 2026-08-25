<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('marketing_offers', 'online_only')) {
            Schema::table('marketing_offers', function (Blueprint $table): void {
                // Por defecto el descuento solo aplica a compras por web/app,
                // igual que se venia comunicando antes de que esto fuera
                // elegible por promocion. El admin puede marcarlo como
                // valido tambien en compras presenciales al crearla.
                $table->boolean('online_only')->default(true)->after('discount_percent');
            });
        }
    }

    public function down(): void
    {
        Schema::table('marketing_offers', function (Blueprint $table): void {
            $table->dropColumn(['online_only']);
        });
    }
};
