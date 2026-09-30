<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollment_documents', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('enrollment_id');
            
            // Type de document
            $table->enum('document_type', [
                'id_card',          // Carte d'identité
                'birth_certificate', // Acte de naissance
                'photo',            // Photo d'identité
                'diploma',          // Diplôme
                'cv',               // CV
                'recommendation',   // Lettre de recommandation
                'other'             // Autre
            ]);
            
            // Informations du fichier
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size'); // en bytes
            $table->string('original_name');
            
            // Statut de vérification
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->ulid('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            
            // Métadonnées
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index(['enrollment_id', 'document_type']);
            $table->index('verification_status');
            
            // Contraintes
            $table->foreign('enrollment_id')
                  ->references('id')->on('enrollments')
                  ->onDelete('cascade');
            $table->foreign('verified_by')
                  ->references('id')->on('users')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_documents');
    }
};
