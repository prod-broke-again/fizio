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
        Schema::table('workout_exercises_v2', function (Blueprint $table) {
            $table->string('video_file')->nullable()->comment('Локальный файл видео')->after('thumbnail_url');
            $table->string('thumbnail_file')->nullable()->comment('Локальный файл превью')->after('video_file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workout_exercises_v2', function (Blueprint $table) {
            $table->dropColumn(['video_file', 'thumbnail_file']);
        });
    }
};
