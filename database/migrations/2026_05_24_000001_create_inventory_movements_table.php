<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name_snapshot', 120);
            $table->string('movement_type', 40);
            $table->enum('direction', ['in', 'out']);
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('stock_before')->default(0);
            $table->unsignedInteger('stock_after')->default(0);
            $table->string('reference_type', 40)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_code', 60)->nullable();
            $table->string('note', 255)->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('role_snapshot', 20)->nullable();
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
            $table->index(['performed_by', 'created_at']);
            $table->index(['role_snapshot', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });

        $adminId = DB::table('users')
            ->where('role', 'admin')
            ->orderBy('id')
            ->value('id');

        $products = DB::table('products')
            ->select('id', 'name', 'stock', 'created_at', 'updated_at')
            ->where('stock', '>', 0)
            ->get();

        foreach ($products as $product) {
            $createdAt = $product->created_at ?: now();
            $updatedAt = $product->updated_at ?: $createdAt;

            DB::table('inventory_movements')->insert([
                'product_id' => $product->id,
                'product_name_snapshot' => $product->name,
                'movement_type' => 'opening',
                'direction' => 'in',
                'quantity' => (int) $product->stock,
                'stock_before' => 0,
                'stock_after' => (int) $product->stock,
                'reference_type' => 'product',
                'reference_id' => $product->id,
                'reference_code' => 'initial-load',
                'note' => 'Carga inicial generada por migracion',
                'performed_by' => $adminId,
                'role_snapshot' => $adminId ? 'admin' : null,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
