<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_chat_messages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('stream_id')->constrained('live_streams')->cascadeOnDelete();
            $table->foreignUlid('session_id')->constrained('live_sessions')->cascadeOnDelete();
            $table->foreignUlid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('message');
            $table->string('type')->default('text'); // text, reaction, system
            $table->string('status')->default('visible'); // visible, hidden, deleted, flagged
            $table->foreignUlid('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->string('moderation_reason')->nullable();
            $table->integer('spam_score')->default(0);
            $table->jsonb('spam_flags')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->jsonb('metadata')->nullable(); // Emoji reactions, mentions, etc.
            $table->timestamps();
            $table->softDeletes();

            $table->index('stream_id');
            $table->index('session_id');
            $table->index('user_id');
            $table->index('status');
            $table->index(['stream_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_chat_messages');
    }
};
