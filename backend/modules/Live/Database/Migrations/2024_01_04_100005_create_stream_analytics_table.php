<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stream_analytics', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('stream_id')->constrained('live_streams')->cascadeOnDelete();
            $table->timestamp('recorded_at');
            $table->integer('concurrent_viewers')->default(0);
            $table->integer('new_viewers')->default(0);
            $table->integer('returning_viewers')->default(0);
            $table->integer('chat_messages')->default(0);
            $table->integer('reactions')->default(0);
            $table->integer('bitrate_kbps')->nullable();
            $table->integer('frame_rate')->nullable();
            $table->decimal('buffer_ratio', 5, 2)->nullable(); // % of viewers experiencing buffering
            $table->jsonb('viewer_locations')->nullable(); // Country distribution
            $table->jsonb('device_breakdown')->nullable(); // Mobile, Desktop, Tablet
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->index('stream_id');
            $table->index('recorded_at');
            $table->index(['stream_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stream_analytics');
    }
};
