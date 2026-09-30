<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prayer_time_history', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('prayer_time_id');
            $table->ulid('changed_by')->nullable(); // User ID qui a fait la modification
            
            // Valeurs avant modification
            $table->time('old_calculated_time')->nullable();
            $table->time('old_manual_time')->nullable();
            $table->boolean('old_is_overridden')->default(false);
            $table->time('old_iqama_time')->nullable();
            
            // Valeurs après modification
            $table->time('new_calculated_time')->nullable();
            $table->time('new_manual_time')->nullable();
            $table->boolean('new_is_overridden')->default(false);
            $table->time('new_iqama_time')->nullable();
            
            // Type de modification
            $table->enum('change_type', ['manual_override', 'remove_override', 'iqama_adjustment', 'recalculation'])->default('manual_override');
            
            // Raison de la modification
            $table->text('reason')->nullable();
            
            // Métadonnées
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamp('changed_at')->useCurrent();
            
            // Index
            $table->index('prayer_time_id');
            $table->index('changed_by');
            $table->index('change_type');
            $table->index('changed_at');
            
            // Contraintes
            $table->foreign('prayer_time_id')
                  ->references('id')->on('prayer_times')
                  ->onDelete('cascade');
            $table->foreign('changed_by')
                  ->references('id')->on('users')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prayer_time_history');
    }
};
