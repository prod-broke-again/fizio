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
        Schema::dropIfExists('foods');

        if (!Schema::hasTable('meals')) {
            Schema::create('meals', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('type'); // breakfast, lunch, dinner, snack
                $table->float('calories')->default(0);
                $table->float('proteins')->default(0);
                $table->float('fats')->default(0);
                $table->float('carbs')->default(0);
                $table->string('food_id')->nullable();
                $table->date('date');
                $table->boolean('completed')->default(false);
                $table->timestamps();
            });
        } else {
            Schema::table('meals', function (Blueprint $table) {
                if (!Schema::hasColumn('meals', 'name')) {
                    $table->string('name')->after('user_id');
                }
                if (!Schema::hasColumn('meals', 'calories')) {
                    $table->float('calories')->default(0)->after('type');
                }
                if (!Schema::hasColumn('meals', 'proteins')) {
                    $table->float('proteins')->default(0)->after('calories');
                }
                if (!Schema::hasColumn('meals', 'fats')) {
                    $table->float('fats')->default(0)->after('proteins');
                }
                if (!Schema::hasColumn('meals', 'carbs')) {
                    $table->float('carbs')->default(0)->after('fats');
                }
                if (!Schema::hasColumn('meals', 'food_id')) {
                    $table->string('food_id')->nullable()->after('carbs');
                }
                if (!Schema::hasColumn('meals', 'date')) {
                    if (Schema::hasColumn('meals', 'consumed_at')) {
                        $table->date('date')->after('food_id');
                    } else {
                        $table->date('date')->after('food_id');
                    }
                }
                if (!Schema::hasColumn('meals', 'completed')) {
                    $table->boolean('completed')->default(false)->after('date');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meals');
    }
}; 