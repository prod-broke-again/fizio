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
        if (!Schema::hasTable('fitness_goals')) {
            Schema::create('fitness_goals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('goal_type'); // weight_loss, muscle_gain, maintenance, etc.
                $table->decimal('target_weight', 5, 2)->nullable();
                $table->integer('target_calories')->nullable();
                $table->integer('target_steps')->nullable();
                $table->integer('target_workout_time')->nullable();
                $table->decimal('target_water_intake', 5, 2)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fitness_goals');
    }
}; 