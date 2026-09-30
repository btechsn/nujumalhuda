<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_streams', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('channel_id')->constrained('live_channels')->cascadeOnDelete();
            $table->jsonb('title'); // {fr: "Khutba du Vendredi", en: "Friday Sermon", ar: "خطبة الجمعة"}
            $table->jsonb('description')->nullable();
            $table->string('type')->default('general'); // general, khutba, recitation, lecture, event
            $table->string('status')->default('scheduled'); // scheduled, live, ended, archived
            $table->string('publish_key'); // Secure stream key for RTMP push
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_seconds')->nullable(); // Calculated after stream ends
            $table->integer('peak_viewers')->default(0);
            $table->integer('total_views')->default(0);
            $table->integer('chat_messages_count')->default(0);
            $table->boolean('enable_chat')->default(true);
            $table->boolean('enable_reactions')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('thumbnail_url')->nullable();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->jsonb('metadata')->nullable(); // Speaker info, event details, etc.
            $table->timestamps();
            $table->softDeletes();

            $table->index('channel_id');
            $table->index('status');
            $table->index('scheduled_at');
            $table->index('is_featured');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_streams');
    }
};
