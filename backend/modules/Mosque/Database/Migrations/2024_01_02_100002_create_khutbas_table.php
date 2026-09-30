<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('khutbas', function (Blueprint $table) {
            $table->char('id', 26)->primary()->comment('ULID');
            $table->char('organization_id', 26)->index();
            
            // Titre multilingue
            $table->jsonb('title_i18n');
            $table->jsonb('summary_i18n')->nullable()->comment('Résumé court');
            
            // Orateur
            $table->char('speaker_id', 26)->nullable()->index()->comment('User/Teacher');
            $table->string('speaker_name')->nullable()->comment('Si orateur externe');
            
            // Date
            $table->date('date')->comment('Vendredi de la khutba');
            $table->time('time')->nullable();
            
            // Contenu
            $table->jsonb('content_i18n')->nullable()->comment('Transcription complète');
            $table->text('key_points')->nullable()->comment('Points clés (liste)');
            
            // Médias
            $table->char('audio_media_id', 26)->nullable()->index();
            $table->char('video_media_id', 26)->nullable()->index();
            $table->string('youtube_url')->nullable();
            
            // Références
            $table->jsonb('references')->nullable()->comment('Versets, hadiths cités');
            
            // État
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
                
            $table->foreign('speaker_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
                
            $table->foreign('audio_media_id')
                ->references('id')
                ->on('media')
                ->nullOnDelete();
                
            $table->foreign('video_media_id')
                ->references('id')
                ->on('media')
                ->nullOnDelete();
                
            $table->index(['date', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('khutbas');
    }
};
