<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Изменяем размер поля energy-kcal_100g с DECIMAL(8,2) на DECIMAL(15,2)
            // Это позволит хранить значения до 9999999999999.99
            $table->decimal('energy-kcal_100g', 15, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Возвращаем обратно к DECIMAL(8,2)
            $table->decimal('energy-kcal_100g', 8, 2)->nullable()->change();
        });
    }
};
