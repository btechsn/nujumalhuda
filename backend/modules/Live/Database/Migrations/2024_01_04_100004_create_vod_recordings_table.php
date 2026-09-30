<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vod_recordings', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('stream_id')->constrained('live_streams')->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->jsonb('title'); // Inherited from stream or custom
            $table->jsonb('description')->nullable();
            $table->string('storage_path');
            $table->string('hls_url');
            $table->string('mp4_url')->nullable();
            $table->integer('duration_seconds');
            $table->integer('file_size_bytes')->nullable();
            $table->string('resolution')->nullable(); // 720p, 1080p, etc.
            $table->integer('bitrate')->nullable(); // kbps
            $table->string('status')->default('processing'); // processing, ready, failed
            $table->integer('views_count')->default(0);
            $table->boolean('is_public')->default(true);
            $table->boolean('is_downloadable')->default(false);
            $table->string('thumbnail_url')->nullable();
            $table->jsonb('chapters')->nullable(); // {0: "Introduction", 300: "Main Topic", ...}
            $table->timestamp('published_at')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('stream_id');
            $table->index('slug');
            $table->index('status');
            $table->index('is_public');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vod_recordings');
    }
};
