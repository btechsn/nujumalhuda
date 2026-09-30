<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->char('id', 26)->primary()->comment('ULID');
            $table->char('user_id', 26)->index()->comment('L\'élève');
            $table->char('promotion_id', 26)->index();
            
            // Statut du dossier (entonnoir d'inscription)
            $table->string('status')->default('pending')->comment('pending, approved, rejected, active, completed, withdrawn');
            $table->timestamp('submitted_at')->nullable()->comment('Date de soumission du dossier');
            $table->timestamp('reviewed_at')->nullable();
            $table->char('reviewed_by', 26)->nullable()->index()->comment('Admin qui a validé');
            $table->text('review_notes')->nullable();
            
            // Informations de l'inscription
            $table->jsonb('application_data')->nullable()->comment('Données du formulaire d\'inscription');
            $table->jsonb('documents')->nullable()->comment('Pièces justificatives uploadées');
            
            // Motivation et prérequis
            $table->text('motivation')->nullable()->comment('Lettre de motivation');
            $table->text('previous_education')->nullable()->comment('Formation antérieure');
            
            // Contact d'urgence
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relation')->nullable();
            
            // Paiement
            $table->boolean('fees_paid')->default(false);
            $table->char('payment_id', 26)->nullable()->index()->comment('Référence vers payments de Core');
            
            // Progression
            $table->unsignedInteger('attendance_rate')->default(0)->comment('Pourcentage de présence (0-100)');
            $table->date('start_date')->nullable()->comment('Date de début effective');
            $table->date('completion_date')->nullable();
            $table->text('completion_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
                
            $table->foreign('promotion_id')
                ->references('id')
                ->on('promotions')
                ->cascadeOnDelete();
                
            $table->foreign('reviewed_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
                
            $table->foreign('payment_id')
                ->references('id')
                ->on('payments')
                ->nullOnDelete();
                
            // Un élève ne peut s'inscrire qu'une seule fois à une promotion
            $table->unique(['user_id', 'promotion_id']);
            
            $table->index(['status', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
