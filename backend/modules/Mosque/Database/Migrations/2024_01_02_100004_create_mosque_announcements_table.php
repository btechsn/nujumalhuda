<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mosque_announcements', function (Blueprint $table) {
            $table->char('id', 26)->primary()->comment('ULID');
            $table->char('organization_id', 26)->index();
            
            // Contenu multilingue
            $table->jsonb('title_i18n');
            $table->jsonb('content_i18n');
            
            // Type
            $table->string('type')->default('general')->comment('general, urgent, maintenance, schedule_change');
            
            // Période d'affichage
            $table->timestamp('display_from');
            $table->timestamp('display_to')->nullable();
            
            // Priorité
            $table->unsignedTinyInteger('priority')->default(1)->comment('1-5, 5 = le plus important');
            
            // État
            $table->boolean('is_active')->default(true);
            $table->boolean('is_pinned')->default(false)->comment('Épinglé en haut');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
                
            $table->index(['display_from', 'display_to', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mosque_announcements');
    }
};
