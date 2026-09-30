<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->char('id', 26)->primary()->comment('ULID');
            $table->char('program_id', 26)->index();
            $table->char('organization_id', 26)->index();
            
            // Identification de la promotion
            $table->jsonb('name_i18n')->comment('Ex: {"fr": "Promotion Coran 2026-2027"}');
            $table->string('code')->unique()->comment('Ex: CORAN-2026-A');
            $table->unsignedInteger('academic_year')->comment('2026, 2027, etc.');
            
            // Période
            $table->date('start_date');
            $table->date('end_date')->nullable();
            
            // Capacité
            $table->unsignedInteger('capacity')->nullable()->comment('Nombre maximal d\'élèves');
            $table->unsignedInteger('min_students')->nullable()->comment('Seuil minimal pour ouvrir');
            
            // Enseignant principal
            $table->char('main_teacher_id', 26)->nullable()->index();
            
            // Horaires
            $table->jsonb('schedule')->nullable()->comment('Jours et heures de cours');
            $table->string('location')->nullable();
            
            // État
            $table->string('status')->default('upcoming')->comment('upcoming, ongoing, completed, cancelled');
            $table->boolean('is_open_for_enrollment')->default(true);
            
            // Statistiques (cache)
            $table->unsignedInteger('enrolled_count')->default(0);
            $table->unsignedInteger('active_count')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('program_id')
                ->references('id')
                ->on('programs')
                ->cascadeOnDelete();
                
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
                
            $table->foreign('main_teacher_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
                
            $table->index(['status', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
