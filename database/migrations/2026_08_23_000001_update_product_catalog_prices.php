<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PRICES = [
        '1/4 Pollo a la Brasa' => 13.50,
        '1/2 Pollo a la Brasa' => 28.00,
        'Pollo Entero a la Brasa' => 65.00,
        'Mostrito Tradicional' => 16.50,
        'Mega Combo Familiar' => 80.00,
        'Parrilla Mixta' => 28.00,
        'Anticuchos x 4' => 26.00,
        'Churrasco a la Parrilla' => 23.00,
        'Alitas BBQ x 8' => 27.00,
        'Brochetas de Pollo' => 26.00,
        'Inca Kola Personal 500ml' => 4.00,
        'Coca-Cola Personal 500ml' => 4.00,
        'Sprite Personal 500ml' => 4.00,
        'Chicha Morada 1L' => 13.00,
        'Maracuya Frozen' => 11.00,
        'Limonada Frozen' => 13.00,
        'Agua Mineral 625ml' => 2.00,
    ];

    public function up(): void
    {
        foreach (self::PRICES as $name => $price) {
            DB::table('products')->where('name', $name)->update(['price' => $price]);
        }
    }

    public function down(): void
    {
        // Cambio de precios intencional del negocio: no hay un valor anterior
        // unico al que revertir de forma segura.
    }
};
