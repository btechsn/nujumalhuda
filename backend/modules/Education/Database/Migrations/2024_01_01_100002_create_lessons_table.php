<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->char('id', 26)->primary()->comment('ULID');
            $table->char('course_id', 26)->index();
            
            // Informations multilingues
            $table->jsonb('title_i18n');
            $table->jsonb('content_i18n')->nullable()->comment('Contenu de la leçon');
            
            // Organisation
            $table->unsignedInteger('sequence')->default(0);
            $table->unsignedInteger('duration_minutes')->nullable();
            
            // Contenu pédagogique
            $table->string('type')->default('theory')->comment('theory, practice, recitation, evaluation');
            $table->jsonb('materials')->nullable()->comment('Documents, audio, vidéo');
            $table->text('notes')->nullable()->comment('Notes de l\'enseignant');
            
            // État
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->cascadeOnDelete();
                
            $table->index(['course_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
