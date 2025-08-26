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
        Schema::table('user_subscriptions_v2', function (Blueprint $table) {
            // Добавляем недостающие колонки
            $table->boolean('is_active')->default(true)->after('expires_at');
            $table->text('notes')->nullable()->after('is_active');
            
            // Убираем колонку starts_at, так как она не используется в модели
            $table->dropColumn('starts_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_subscriptions_v2', function (Blueprint $table) {
            // Восстанавливаем исходное состояние
            $table->timestamp('starts_at');
            $table->dropColumn(['is_active', 'notes']);
        });
    }
};
