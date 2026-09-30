<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moderations', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            
            $table->string('moderatable_type');
            $table->char('moderatable_id', 26);
            
            $table->string('status', 20)->default('pending')->index();
            $table->char('moderated_by', 26)->nullable();
            $table->timestampTz('moderated_at')->nullable();
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestampsTz();
            
            $table->index(['moderatable_type', 'moderatable_id']);
            $table->index(['status', 'created_at']);
            
            $table->foreign('moderated_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            
            $table->check('status IN (\'pending\', \'approved\', \'rejected\', \'flagged\')');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moderations');
    }
};
