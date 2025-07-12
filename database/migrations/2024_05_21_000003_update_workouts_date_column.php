<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workouts', function (Blueprint $table) {
            if (Schema::hasColumn('workouts', 'date')) {
                $table->date('date')->nullable()->change();
            } else {
                $table->date('date')->nullable()->after('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('workouts', function (Blueprint $table) {
            $table->date('date')->nullable(false)->change();
        });
    }
}; 