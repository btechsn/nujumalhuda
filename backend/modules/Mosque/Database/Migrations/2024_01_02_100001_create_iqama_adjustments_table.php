<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iqama_adjustments', function (Blueprint $table) {
            $table->char('id', 26)->primary()->comment('ULID');
            $table->char('organization_id', 26)->index();
            
            // Prière concernée
            $table->string('prayer_name')->comment('fajr, dhuhr, asr, maghrib, isha');
            
            // Décalage en minutes entre adhan et iqama
            $table->unsignedInteger('minutes_offset')->default(15)->comment('Décalage en minutes');
            
            // Période de validité
            $table->date('valid_from');
            $table->date('valid_to')->nullable()->comment('Null = indéfini');
            
            // Description
            $table->text('description')->nullable()->comment('Raison du décalage (hiver, Ramadan, etc.)');
            
            // État
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
                
            $table->index(['organization_id', 'prayer_name', 'valid_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iqama_adjustments');
    }
};
