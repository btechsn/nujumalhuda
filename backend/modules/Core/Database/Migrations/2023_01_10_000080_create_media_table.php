<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            
            $table->string('mediable_type')->nullable();
            $table->char('mediable_id', 26)->nullable();
            $table->char('uploaded_by', 26)->nullable();
            
            $table->string('collection', 50)->default('default')->index();
            $table->string('name');
            $table->string('file_name');
            $table->string('mime_type', 100);
            $table->string('disk', 20)->default('public');
            $table->string('path');
            $table->unsignedBigInteger('size');
            
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedInteger('duration')->nullable();
            $table->jsonb('metadata')->nullable();
            
            $table->timestampsTz();
            
            $table->index(['mediable_type', 'mediable_id']);
            
            $table->foreign('uploaded_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
