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
        Schema::table('workouts', function (Blueprint $table) {
            if (!Schema::hasColumn('workouts', 'image_url')) {
                $table->string('image_url')->nullable()->after('difficulty'); // Или другое место, если нужно
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workouts', function (Blueprint $table) {
            if (Schema::hasColumn('workouts', 'image_url')) {
                $table->dropColumn('image_url');
            }
        });
    }
}; 