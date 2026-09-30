<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->char('id', 26)->primary()->comment('ULID');
            $table->char('program_id', 26)->index();
            
            // Informations multilingues
            $table->jsonb('name_i18n');
            $table->jsonb('description_i18n')->nullable();
            $table->jsonb('objectives_i18n')->nullable();
            
            // Organisation
            $table->string('code')->unique()->comment('Ex: CORAN-01, ARABE-02');
            $table->unsignedInteger('sequence')->default(0)->comment('Ordre dans le programme');
            $table->unsignedInteger('duration_hours')->nullable();
            
            // Contenu
            $table->jsonb('syllabus_i18n')->nullable()->comment('Plan du cours détaillé');
            $table->jsonb('resources')->nullable()->comment('Livres, supports, liens');
            
            // État
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('program_id')
                ->references('id')
                ->on('programs')
                ->cascadeOnDelete();
                
            $table->index(['program_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
