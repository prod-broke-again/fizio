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
        Schema::create('workout_exercises_v2', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('video_url')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->integer('sets')->default(1);
            $table->integer('reps')->default(1);
            $table->integer('rest_seconds')->default(0);
            $table->json('equipment_needed')->nullable();
            $table->json('muscle_groups')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->foreign('program_id')->references('id')->on('workout_programs_v2')->onDelete('cascade');
            $table->index(['program_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_exercises_v2');
    }
};
