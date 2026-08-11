<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop existing session_usages table to align with requested columns
        Schema::dropIfExists('session_usages');

        // 2. Create session_presence_logs table
        Schema::create('session_presence_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('book_session_id')->constrained('book_sessions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->timestamps();
        });

        // 3. Recreate session_usages table with the correct fields
        Schema::create('session_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_session_id')->unique()->constrained('book_sessions')->onDelete('cascade');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('timer_started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('used_seconds')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_usages');
        Schema::dropIfExists('session_presence_logs');
    }
};
