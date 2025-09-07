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
        Schema::table('workout_programs_v2', function (Blueprint $table) {
            $table->string('video_url')->nullable()->comment('URL видео программы тренировок')->after('calories_per_workout');
            $table->string('thumbnail_url')->nullable()->comment('URL превью видео')->after('video_url');
            $table->string('video_file')->nullable()->comment('Локальный файл видео')->after('thumbnail_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workout_programs_v2', function (Blueprint $table) {
            $table->dropColumn(['video_url', 'thumbnail_url', 'video_file']);
        });
    }
};
