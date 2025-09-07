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
            $table->text('instructions')->nullable()->comment('Инструкции по выполнению упражнения')->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workout_exercises_v2', function (Blueprint $table) {
            $table->dropColumn('instructions');
        });
    }
};
