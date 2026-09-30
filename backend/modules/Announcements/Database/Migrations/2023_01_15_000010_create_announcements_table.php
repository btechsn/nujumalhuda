<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            
            $table->jsonb('title');
            $table->jsonb('message');
            
            $table->string('category', 20)->default('center')->index();
            $table->string('priority', 10)->default('normal')->index();
            
            $table->timestampTz('starts_at')->nullable();
            $table->timestampTz('ends_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
            
            $table->string('action_url')->nullable();
            $table->jsonb('metadata')->nullable();
            
            $table->timestampsTz();
            
            $table->index(['is_active', 'starts_at', 'ends_at']);
            $table->check('category IN (\'center\', \'community\', \'urgent\', \'event\')');
            $table->check('priority IN (\'low\', \'normal\', \'high\')');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
