<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->char('id', 26)->primary()->comment('ULID');
            $table->char('organization_id', 26)->index();
            
            // Informations multilingues
            $table->jsonb('name_i18n')->comment('{"fr": "...", "en": "...", "ar": "..."}');
            $table->jsonb('description_i18n')->nullable();
            $table->jsonb('objectives_i18n')->nullable()->comment('Objectifs pédagogiques');
            
            // Détails du programme
            $table->string('type')->comment('coran, arabe, baye_niasse, sunnite');
            $table->string('level')->comment('debutant, intermediaire, avance');
            $table->unsignedInteger('duration_weeks')->nullable()->comment('Durée indicative en semaines');
            $table->unsignedInteger('hours_per_week')->nullable();
            
            // Tarification (en minor units XOF)
            $table->unsignedBigInteger('tuition_amount_minor')->nullable()->comment('Frais de scolarité');
            $table->unsignedBigInteger('registration_amount_minor')->nullable()->comment('Frais d\'inscription');
            $table->string('currency', 3)->default('XOF');
            
            // Prérequis et restrictions
            $table->unsignedInteger('min_age')->nullable();
            $table->unsignedInteger('max_age')->nullable();
            $table->char('prerequisite_program_id', 26)->nullable()->index()->comment('Programme prérequis');
            
            // État
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false)->comment('Mis en avant sur la page d\'accueil');
            $table->unsignedInteger('display_order')->default(0);
            
            // Méta
            $table->jsonb('metadata')->nullable()->comment('Images, icônes, badges');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Relations
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
                
            $table->foreign('prerequisite_program_id')
                ->references('id')
                ->on('programs')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
