<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->char('id', 26)->primary()->comment('ULID');
            $table->char('author_id', 26)->index();
            $table->char('category_id', 26)->nullable()->index();
            $table->char('organization_id', 26)->index();
            
            // Contenu multilingue
            $table->jsonb('title_i18n');
            $table->jsonb('excerpt_i18n')->nullable()->comment('Résumé court');
            $table->jsonb('content_i18n')->comment('Contenu HTML');
            
            // Slug et SEO
            $table->string('slug')->unique();
            $table->jsonb('meta_description_i18n')->nullable();
            $table->jsonb('meta_keywords')->nullable();
            
            // Image de couverture
            $table->char('cover_image_id', 26)->nullable()->index();
            
            // État de publication
            $table->string('status')->default('draft')->comment('draft, published, archived');
            $table->timestamp('published_at')->nullable();
            
            // Statistiques
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            
            // Tags
            $table->jsonb('tags')->nullable();
            
            // Options
            $table->boolean('is_featured')->default(false)->comment('Article mis en avant');
            $table->boolean('allow_comments')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('author_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
                
            $table->foreign('category_id')
                ->references('id')
                ->on('article_categories')
                ->nullOnDelete();
                
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
                
            $table->foreign('cover_image_id')
                ->references('id')
                ->on('media')
                ->nullOnDelete();
                
            $table->index(['status', 'published_at']);
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
