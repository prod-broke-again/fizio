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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'apple_watch_connected')) {
                $table->boolean('apple_watch_connected')->default(false);
            }
            if (!Schema::hasColumn('users', 'last_sync_at')) {
                $table->timestamp('last_sync_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['apple_watch_connected', 'last_sync_at']);
        });
    }
}; 