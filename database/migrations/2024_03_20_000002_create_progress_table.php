<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('progress')) {
            Schema::create('progress', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->date('date');
                $table->integer('calories')->nullable();
                $table->integer('steps')->nullable();
                $table->integer('workout_time')->nullable();
                $table->decimal('water_intake', 5, 2)->nullable();
                $table->float('weight')->nullable();
                $table->float('body_fat_percentage')->nullable();
                $table->json('measurements')->nullable();
                $table->json('photos')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'date']);
            });
        } else {
            Schema::table('progress', function (Blueprint $table) {
                if (!Schema::hasColumn('progress', 'id')) {
                    // Это сложный случай, если id не был UUID. 
                    // Для простоты предполагаем, что если таблица есть, но без id, то что-то не так.
                    // Или же, если id был integer, его преобразование в UUID требует отдельной миграции данных.
                    // Пока что, если id нет, не будем его создавать в alter, это должно быть в create.
                } else if (DB::getDriverName() !== 'sqlite') { // SQLite не поддерживает изменение типа первичного ключа таким образом
                    // Попытка изменить тип id на UUID, если он был другим (например, bigInteger)
                    // ВНИМАНИЕ: Это может не сработать на всех БД или потребовать удаления и пересоздания ключей.
                    // $table->uuid('id')->change(); // Опасно для существующих данных без четкой стратегии миграции
                }

                if (!Schema::hasColumn('progress', 'weight')) {
                    $table->float('weight')->nullable()->after('water_intake');
                }
                if (!Schema::hasColumn('progress', 'body_fat_percentage')) {
                    $table->float('body_fat_percentage')->nullable()->after('weight');
                }
                if (!Schema::hasColumn('progress', 'measurements')) {
                    $table->json('measurements')->nullable()->after('body_fat_percentage');
                }
                if (!Schema::hasColumn('progress', 'photos')) {
                    $table->json('photos')->nullable()->after('measurements');
                }

                if (Schema::hasColumn('progress', 'calories')) {
                    $table->integer('calories')->nullable()->change();
                }
                if (Schema::hasColumn('progress', 'steps')) {
                    $table->integer('steps')->nullable()->change();
                }
                if (Schema::hasColumn('progress', 'workout_time')) {
                    $table->integer('workout_time')->nullable()->change();
                }
                if (Schema::hasColumn('progress', 'water_intake')) {
                    $table->decimal('water_intake', 5, 2)->nullable()->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress');
    }
}; 