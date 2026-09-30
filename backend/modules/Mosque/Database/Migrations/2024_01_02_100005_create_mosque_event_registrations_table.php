<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mosque_event_registrations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('organization_id');
            $table->ulid('event_id');
            $table->ulid('user_id')->nullable(); // Si connecté
            
            // Informations du participant
            $table->string('participant_name');
            $table->string('participant_email');
            $table->string('participant_phone')->nullable();
            $table->integer('number_of_attendees')->default(1);
            $table->text('message')->nullable();
            
            // Statut de l'inscription
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'attended'])->default('pending');
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            // Métadonnées
            $table->string('confirmation_code', 32)->unique()->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index(['organization_id', 'event_id']);
            $table->index(['event_id', 'status']);
            $table->index('user_id');
            $table->index('participant_email');
            $table->index('confirmation_code');
            
            // Contraintes
            $table->foreign('organization_id')
                  ->references('id')->on('organizations')
                  ->onDelete('cascade');
            $table->foreign('event_id')
                  ->references('id')->on('mosque_events')
                  ->onDelete('cascade');
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mosque_event_registrations');
    }
};
