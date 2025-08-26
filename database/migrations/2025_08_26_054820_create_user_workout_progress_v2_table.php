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
        Schema::create('user_workout_progress_v2', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('program_id');
            $table->foreignId('exercise_id');
            $table->timestamp('completed_at');
            $table->integer('duration_seconds')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('program_id')->references('id')->on('workout_programs_v2')->onDelete('cascade');
            $table->foreign('exercise_id')->references('id')->on('workout_exercises_v2')->onDelete('cascade');
            $table->index(['user_id', 'program_id']);
            $table->index('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_workout_progress_v2');
    }
};
