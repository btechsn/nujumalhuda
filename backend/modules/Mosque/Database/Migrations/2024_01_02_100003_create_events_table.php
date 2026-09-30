<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mosque_events', function (Blueprint $table) {
            $table->char('id', 26)->primary()->comment('ULID');
            $table->char('organization_id', 26)->index();
            
            // Informations multilingues
            $table->jsonb('title_i18n');
            $table->jsonb('description_i18n')->nullable();
            
            // Type d'événement
            $table->string('type')->comment('lecture, conference, special_prayer, fundraising, community');
            
            // Dates
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_pattern')->nullable()->comment('weekly, monthly, etc.');
            
            // Lieu
            $table->string('location')->nullable();
            $table->text('location_details')->nullable();
            
            // Intervenant
            $table->char('speaker_id', 26)->nullable()->index();
            $table->string('speaker_name')->nullable()->comment('Si externe');
            
            // Capacité et inscriptions
            $table->unsignedInteger('capacity')->nullable();
            $table->boolean('requires_registration')->default(false);
            $table->unsignedInteger('registered_count')->default(0);
            
            // Médias
            $table->char('image_media_id', 26)->nullable()->index();
            
            // État
            $table->string('status')->default('upcoming')->comment('upcoming, ongoing, completed, cancelled');
            $table->boolean('is_featured')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
                
            $table->foreign('speaker_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
                
            $table->foreign('image_media_id')
                ->references('id')
                ->on('media')
                ->nullOnDelete();
                
            $table->index(['start_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mosque_events');
    }
};
