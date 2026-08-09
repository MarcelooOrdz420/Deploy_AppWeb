<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('job_openings')) {
            Schema::create('job_openings', function (Blueprint $table): void {
                $table->id();
                $table->string('title', 120);
                $table->string('description', 500)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }
    public function down(): void { Schema::dropIfExists('job_openings'); }
};
