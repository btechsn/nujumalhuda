<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_sessions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('stream_id')->constrained('live_streams')->cascadeOnDelete();
            $table->foreignUlid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('session_token')->unique();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device_type')->nullable(); // mobile, desktop, tablet
            $table->string('browser')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->timestamp('joined_at');
            $table->timestamp('left_at')->nullable();
            $table->integer('watch_duration_seconds')->default(0);
            $table->integer('messages_sent')->default(0);
            $table->integer('reactions_sent')->default(0);
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->index('stream_id');
            $table->index('user_id');
            $table->index('joined_at');
            $table->index(['stream_id', 'joined_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_sessions');
    }
};
