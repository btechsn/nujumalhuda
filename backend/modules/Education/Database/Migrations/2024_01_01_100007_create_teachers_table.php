<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->char('id', 26)->primary()->comment('ULID, même que user_id');
            $table->char('organization_id', 26)->index();
            
            // Profil enseignant
            $table->jsonb('bio_i18n')->nullable()->comment('Biographie trilingue');
            $table->jsonb('specialties_i18n')->nullable()->comment('Spécialités : Coran, hadith, fiqh, etc.');
            $table->jsonb('qualifications_i18n')->nullable()->comment('Diplômes et formations');
            
            // Sanad (chaîne de transmission)
            $table->jsonb('sanad')->nullable()->comment('Chaîne de transmission si ijaza');
            $table->boolean('has_ijaza')->default(false);
            $table->jsonb('ijaza_details')->nullable()->comment('Détails de l\'ijaza reçue');
            
            // Disponibilité
            $table->jsonb('availability')->nullable()->comment('Jours et heures disponibles');
            $table->boolean('is_available')->default(true);
            
            // Médias
            $table->char('photo_media_id', 26)->nullable()->index();
            $table->string('youtube_channel')->nullable();
            $table->string('facebook_page')->nullable();
            
            // Statistiques (cache)
            $table->unsignedInteger('students_count')->default(0);
            $table->unsignedInteger('courses_taught')->default(0);
            $table->unsignedInteger('total_sessions')->default(0);
            
            // État
            $table->boolean('is_featured')->default(false)->comment('Affiché sur la page enseignants');
            $table->unsignedInteger('display_order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // L'id du teacher est le même que celui du user
            $table->foreign('id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
                
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
                
            $table->foreign('photo_media_id')
                ->references('id')
                ->on('media')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
