<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prayer_times', function (Blueprint $table) {
            $table->char('id', 26)->primary()->comment('ULID');
            $table->char('organization_id', 26)->index();
            
            // Date et prière
            $table->date('date');
            $table->string('prayer_name')->comment('fajr, dhuhr, asr, maghrib, isha');
            
            // Horaires calculés (API Aladhan)
            $table->time('calculated_time')->nullable()->comment('Heure calculée automatiquement');
            $table->string('calculation_method')->nullable()->comment('MWL, ISNA, Egypt, etc.');
            
            // Override manuel (L'IMAM A TOUJOURS PRIORITÉ)
            $table->time('manual_time')->nullable()->comment('Heure saisie manuellement par l\'imam');
            $table->boolean('is_overridden')->default(false)->comment('True si manual_time est renseigné');
            $table->char('overridden_by', 26)->nullable()->index()->comment('Admin qui a fait l\'override');
            $table->timestamp('overridden_at')->nullable();
            $table->text('override_reason')->nullable();
            
            // Iqama (adhan + décalage)
            $table->time('iqama_time')->nullable()->comment('Heure de l\'iqama (calculée avec décalage)');
            
            // Métadonnées
            $table->jsonb('metadata')->nullable()->comment('Coordonnées, timezone, etc.');
            
            $table->timestamps();
            
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
                
            $table->foreign('overridden_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
                
            // Une seule entrée par date/prière/organisation
            $table->unique(['organization_id', 'date', 'prayer_name']);
            
            $table->index(['date', 'prayer_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prayer_times');
    }
};
