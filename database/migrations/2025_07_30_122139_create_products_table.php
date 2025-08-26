<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            
            // Основная информация
            $table->string('code', 50)->unique()->index(); // штрихкод
            $table->longText('product_name')->nullable();
            $table->longText('generic_name')->nullable();
            $table->longText('brands')->nullable();
            $table->longText('categories')->nullable();
            $table->longText('ingredients_text')->nullable();
            $table->longText('countries')->nullable();
            $table->string('quantity', 100)->nullable();
            $table->longText('packaging')->nullable();
            
            // Изображения
            $table->longText('image_url')->nullable();
            $table->longText('image_small_url')->nullable();
            $table->longText('image_ingredients_url')->nullable();
            $table->longText('image_nutrition_url')->nullable();
            
            // Питательная ценность
            $table->string('nutriscore_grade', 10)->nullable();
            $table->string('nova_group', 10)->nullable();
            $table->decimal('energy-kcal_100g', 8, 2)->nullable();
            $table->decimal('proteins_100g', 8, 2)->nullable();
            $table->decimal('carbohydrates_100g', 8, 2)->nullable();
            $table->decimal('fat_100g', 8, 2)->nullable();
            $table->decimal('fiber_100g', 8, 2)->nullable();
            $table->decimal('salt_100g', 8, 2)->nullable();
            $table->decimal('sugars_100g', 8, 2)->nullable();
            $table->decimal('saturated-fat_100g', 8, 2)->nullable();
            
            // Дополнительная информация
            $table->longText('allergens')->nullable();
            $table->longText('traces')->nullable();
            $table->longText('additives')->nullable();
            $table->longText('labels')->nullable();
            $table->longText('origins')->nullable();
            $table->longText('manufacturing_places')->nullable();
            
            // Метаданные
            $table->timestamp('created_t')->nullable();
            $table->timestamp('last_modified_t')->nullable();
            $table->integer('unique_scans_n')->default(0);
            $table->decimal('completeness', 5, 2)->nullable();
            
            $table->timestamps();
            
            // Индексы для быстрого поиска
            $table->index(['nutriscore_grade']);
            $table->index(['nova_group']);
            $table->index(['unique_scans_n']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}; 