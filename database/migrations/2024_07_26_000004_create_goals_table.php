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
        if (!Schema::hasTable('goals')) {
            Schema::create('goals', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('type');
                $table->float('target_value');
                $table->float('current_value')->nullable();
                $table->float('start_value')->nullable();
                $table->string('unit');
                $table->date('target_date')->nullable();
                $table->string('status')->default('active'); // active, completed, abandoned
                $table->json('notes')->nullable(); // Для записей о прогрессе к цели
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
}; 