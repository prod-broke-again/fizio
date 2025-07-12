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
        Schema::dropIfExists('user_workout_favorites');

        Schema::dropIfExists('workouts');

            Schema::create('workouts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('type');
            $table->string('category')->nullable();
            $table->json('exercises');
            $table->integer('duration');
            $table->string('difficulty');
            $table->string('image_url')->nullable();
            $table->date('date');
            $table->boolean('completed')->default(false);
            $table->integer('calories_burned')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workouts');
    }
}; 