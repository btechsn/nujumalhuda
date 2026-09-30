<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audio_recitations', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->unsignedTinyInteger('surah_number');
            $table->jsonb('surah_name_i18n');
            $table->string('reciter');
            $table->string('audio_url');
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->boolean('is_public')->default(true);
            $table->unsignedInteger('play_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['surah_number', 'reciter']);
            $table->index('is_public');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audio_recitations');
    }
};
