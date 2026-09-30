<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->char('id', 26)->primary()->comment('ULID');
            $table->char('enrollment_id', 26)->index();
            $table->char('session_id', 26)->index();
            
            // Présence
            $table->string('status')->default('present')->comment('present, absent, late, excused');
            $table->timestamp('marked_at')->nullable()->comment('Quand la présence a été marquée');
            $table->char('marked_by', 26)->nullable()->index()->comment('Enseignant qui a marqué');
            
            // Justification
            $table->text('excuse')->nullable()->comment('Justification d\'absence');
            $table->boolean('is_excused')->default(false);
            
            // Notes de participation
            $table->text('notes')->nullable()->comment('Notes de l\'enseignant sur la participation');
            $table->unsignedTinyInteger('participation_score')->nullable()->comment('0-10');
            
            $table->timestamps();
            
            $table->foreign('enrollment_id')
                ->references('id')
                ->on('enrollments')
                ->cascadeOnDelete();
                
            $table->foreign('session_id')
                ->references('id')
                ->on('sessions')
                ->cascadeOnDelete();
                
            $table->foreign('marked_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
                
            // Un élève ne peut avoir qu'une seule entrée de présence par session
            $table->unique(['enrollment_id', 'session_id']);
            
            $table->index(['session_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
