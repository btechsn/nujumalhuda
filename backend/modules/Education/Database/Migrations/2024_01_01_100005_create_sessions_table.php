<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->char('id', 26)->primary()->comment('ULID');
            $table->char('course_id', 26)->index();
            $table->char('promotion_id', 26)->index();
            $table->char('lesson_id', 26)->nullable()->index()->comment('Leçon couverte (optionnel)');
            $table->char('teacher_id', 26)->nullable()->index();
            
            // Planification
            $table->jsonb('title_i18n');
            $table->dateTime('scheduled_at');
            $table->unsignedInteger('duration_minutes')->default(60);
            $table->string('location')->nullable();
            
            // Type de session
            $table->string('type')->default('lecture')->comment('lecture, practical, recitation, evaluation, revision');
            
            // Contenu
            $table->jsonb('description_i18n')->nullable();
            $table->text('notes')->nullable()->comment('Notes de l\'enseignant');
            $table->jsonb('materials')->nullable()->comment('Supports utilisés');
            
            // Statut
            $table->string('status')->default('scheduled')->comment('scheduled, ongoing, completed, cancelled');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            
            // Lien avec le streaming (Phase 3)
            $table->char('live_session_id', 26)->nullable()->index()->comment('Référence vers Live module');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->cascadeOnDelete();
                
            $table->foreign('promotion_id')
                ->references('id')
                ->on('promotions')
                ->cascadeOnDelete();
                
            $table->foreign('lesson_id')
                ->references('id')
                ->on('lessons')
                ->nullOnDelete();
                
            $table->foreign('teacher_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
                
            $table->index(['promotion_id', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
