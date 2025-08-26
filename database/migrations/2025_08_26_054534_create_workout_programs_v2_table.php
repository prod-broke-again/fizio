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
        Schema::create('workout_programs_v2', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('short_description')->nullable();
            $table->enum('difficulty_level', ['beginner', 'intermediate', 'advanced']);
            $table->integer('duration_weeks')->default(1);
            $table->integer('calories_per_workout')->nullable();
            $table->boolean('is_free')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->foreign('category_id')->references('id')->on('workout_categories_v2')->onDelete('cascade');
            $table->index(['category_id', 'is_active']);
            $table->index(['is_free', 'is_active']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_programs_v2');
    }
};
