<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_categories', function (Blueprint $table) {
            $table->char('id', 26)->primary()->comment('ULID');
            
            // Nom multilingue
            $table->jsonb('name_i18n');
            $table->jsonb('description_i18n')->nullable();
            
            // Slug
            $table->string('slug')->unique();
            
            // Ordre et état
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_categories');
    }
};
