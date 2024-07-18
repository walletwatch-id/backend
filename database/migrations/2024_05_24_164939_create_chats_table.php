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
        Schema::create('chat_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('external_id')->nullable();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('external_id')->nullable();
            $table->foreignUuid('session_id')->constrained('chat_sessions')->cascadeOnDelete();
            $table->enum('sender', ['USER', 'BOT']);
            $table->text('message');
            $table->string('hash')->nullable();
            $table->enum('status', [
                'CREATED', 'QUEUED', 'IN_PROGRESS', 'CANCELLED', 'FAILED', 'COMPLETED',
                'INCOMPLETE', 'EXPIRED',
            ])->default('CREATED');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_sessions');
        Schema::dropIfExists('chat_messages');
    }
};
