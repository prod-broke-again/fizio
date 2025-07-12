<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->enum('type', [
                'workout', 'water', 'achievement', 'system', 'profile', 'health', 'reminder'
            ]);
            $table->boolean('read')->default(false);
            $table->string('action')->nullable();
            $table->string('action_url')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'read']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}; 