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
        Schema::create('user_subscriptions_v2', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->enum('subscription_type', ['monthly', 'yearly', 'lifetime']);
            $table->enum('status', ['active', 'expired', 'cancelled']);
            $table->timestamp('starts_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'status']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions_v2');
    }
};
