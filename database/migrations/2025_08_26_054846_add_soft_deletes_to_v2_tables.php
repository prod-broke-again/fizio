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
        Schema::table('workout_categories_v2', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('workout_programs_v2', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('workout_exercises_v2', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('user_subscriptions_v2', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('user_workout_progress_v2', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workout_categories_v2', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('workout_programs_v2', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('workout_exercises_v2', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('user_subscriptions_v2', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('user_workout_progress_v2', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
