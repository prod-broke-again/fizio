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
        Schema::create('meal_items', function (Blueprint $table) {
            $table->id();
            $table->string('meal_id'); // Изменено на string для поддержки UUID
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');
            $table->string('free_text')->nullable()->comment('Свободный текст продукта, если product_id не указан');
            $table->decimal('grams', 8, 2)->nullable()->comment('Вес в граммах');
            $table->decimal('servings', 6, 2)->nullable()->comment('Количество порций');
            $table->decimal('calories', 8, 2)->default(0)->comment('Калории на позицию');
            $table->decimal('proteins', 8, 2)->default(0)->comment('Белки на позицию (г)');
            $table->decimal('fats', 8, 2)->default(0)->comment('Жиры на позицию (г)');
            $table->decimal('carbs', 8, 2)->default(0)->comment('Углеводы на позицию (г)');
            $table->timestamps();
            
            // Индексы для быстрого поиска
            $table->index(['meal_id', 'product_id']);
            $table->index('free_text');
            
            // Foreign key constraint для meal_id
            $table->foreign('meal_id')->references('id')->on('meals')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_items');
    }
};
