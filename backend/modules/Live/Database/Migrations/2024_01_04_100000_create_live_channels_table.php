<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_channels', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('slug')->unique(); // main, recitation, audio
            $table->jsonb('name'); // {fr: "Canal Principal", en: "Main Channel", ar: "القناة الرئيسية"}
            $table->jsonb('description')->nullable();
            $table->string('type')->default('video'); // video, audio
            $table->string('rtmp_ingest_url');
            $table->string('whep_url');
            $table->string('hls_url');
            $table->integer('max_bitrate')->nullable(); // kbps
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_moderation')->default(false);
            $table->jsonb('metadata')->nullable(); // Additional settings
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_channels');
    }
};
