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
            // Изменяем размер всех числовых полей питания с DECIMAL(8,2) на DECIMAL(15,2)
            // Это позволит хранить большие значения без переполнения
            $table->decimal('proteins_100g', 15, 2)->nullable()->change();
            $table->decimal('carbohydrates_100g', 15, 2)->nullable()->change();
            $table->decimal('fat_100g', 15, 2)->nullable()->change();
            $table->decimal('fiber_100g', 15, 2)->nullable()->change();
            $table->decimal('salt_100g', 15, 2)->nullable()->change();
            $table->decimal('sugars_100g', 15, 2)->nullable()->change();
            $table->decimal('saturated-fat_100g', 15, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Возвращаем обратно к DECIMAL(8,2)
            $table->decimal('proteins_100g', 8, 2)->nullable()->change();
            $table->decimal('carbohydrates_100g', 8, 2)->nullable()->change();
            $table->decimal('fat_100g', 8, 2)->nullable()->change();
            $table->decimal('fiber_100g', 8, 2)->nullable()->change();
            $table->decimal('salt_100g', 8, 2)->nullable()->change();
            $table->decimal('sugars_100g', 8, 2)->nullable()->change();
            $table->decimal('saturated-fat_100g', 8, 2)->nullable()->change();
        });
    }
};
