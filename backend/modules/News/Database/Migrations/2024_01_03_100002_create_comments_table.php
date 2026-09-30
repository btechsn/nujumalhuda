<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_comments', function (Blueprint $table) {
            $table->char('id', 26)->primary()->comment('ULID');
            $table->char('article_id', 26)->index();
            $table->char('user_id', 26)->index();
            $table->char('parent_id', 26)->nullable()->index()->comment('Réponse à un commentaire');
            
            // Contenu
            $table->text('content');
            
            // Modération (réutilise la table moderations de Core)
            $table->string('status')->default('pending')->comment('pending, approved, rejected, flagged');
            $table->timestamp('moderated_at')->nullable();
            $table->char('moderated_by', 26)->nullable()->index();
            $table->text('moderation_reason')->nullable();
            
            // Signalements
            $table->unsignedInteger('reports_count')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('article_id')
                ->references('id')
                ->on('articles')
                ->cascadeOnDelete();
                
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
                
            $table->foreign('parent_id')
                ->references('id')
                ->on('article_comments')
                ->cascadeOnDelete();
                
            $table->foreign('moderated_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
                
            $table->index(['article_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_comments');
    }
};
