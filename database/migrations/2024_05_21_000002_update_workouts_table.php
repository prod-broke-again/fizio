<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workouts', function (Blueprint $table) {
            if (Schema::hasColumn('workouts', 'user_id')) {
                $table->foreignId('user_id')->nullable()->change();
            } else {
                $table->foreignId('user_id')->nullable()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('workouts', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
}; 