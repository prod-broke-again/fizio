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
        Schema::table('user_workout_progress_v2', function (Blueprint $table) {
            // Добавляем недостающие колонки
            $table->integer('sets_completed')->nullable()->after('duration_seconds');
            $table->integer('reps_completed')->nullable()->after('sets_completed');
            $table->decimal('weight_used_kg', 5, 2)->nullable()->after('reps_completed');
            $table->integer('calories_burned')->nullable()->after('weight_used_kg');
            $table->boolean('is_completed')->default(true)->after('calories_burned');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_workout_progress_v2', function (Blueprint $table) {
            // Восстанавливаем исходное состояние
            $table->dropColumn(['sets_completed', 'reps_completed', 'weight_used_kg', 'calories_burned', 'is_completed']);
        });
    }
};
