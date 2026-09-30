<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('platform');
            $table->string('handle');
            $table->string('url');
            $table->string('embed_url')->nullable();
            $table->boolean('redirect_only')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['platform', 'handle']);
        });

        Schema::create('recitation_sessions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('trigger');
            $table->ulid('live_stream_id')->nullable();
            $table->char('student_id', 26)->nullable();
            $table->char('teacher_id', 26)->nullable();
            $table->char('milestone_id', 26)->nullable();
            $table->string('title');
            $table->timestamp('starts_at')->nullable();
            $table->string('status')->default('scheduled');
            $table->timestamps();

            $table->index(['trigger', 'status']);
            $table->index(['student_id', 'milestone_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recitation_sessions');
        Schema::dropIfExists('social_accounts');
    }
};
