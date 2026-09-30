<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcement_audiences', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            
            $table->char('announcement_id', 26);
            $table->string('type', 20);
            $table->char('target_id', 26)->nullable();
            
            $table->timestampsTz();
            
            $table->foreign('announcement_id')
                ->references('id')
                ->on('announcements')
                ->cascadeOnDelete();
            
            $table->index(['announcement_id', 'type']);
            $table->index(['type', 'target_id']);
            
            $table->check('type IN (\'public\', \'members\', \'role\', \'organization\', \'cohort\')');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_audiences');
    }
};
