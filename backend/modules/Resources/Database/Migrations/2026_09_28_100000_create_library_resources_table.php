<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_resources', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->string('slug')->unique();
            $table->jsonb('title_i18n');
            $table->jsonb('description_i18n')->nullable();
            $table->string('author')->nullable();
            $table->string('tradition')->comment('baye_niasse, sunnite');
            $table->string('kind')->comment('pdf, audio');
            $table->string('language', 5)->default('ar');
            $table->string('file_path')->nullable();
            $table->string('external_url')->nullable();
            $table->unsignedInteger('file_size_bytes')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->boolean('is_public')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tradition', 'is_public']);
            $table->index('kind');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_resources');
    }
};
