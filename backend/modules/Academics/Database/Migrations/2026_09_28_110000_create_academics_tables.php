<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hifz_milestones', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->string('slug')->unique();
            $table->jsonb('title_i18n');
            $table->jsonb('description_i18n')->nullable();
            $table->unsignedTinyInteger('from_juz')->nullable();
            $table->unsignedTinyInteger('to_juz')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
        });

        Schema::create('badges', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('milestone_id', 26)->unique();
            $table->jsonb('name_i18n');
            $table->jsonb('description_i18n')->nullable();
            $table->string('icon')->default('star');
            $table->timestamps();

            $table->foreign('milestone_id')->references('id')->on('hifz_milestones')->cascadeOnDelete();
        });

        Schema::create('student_progress', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('student_id', 26);
            $table->char('milestone_id', 26);
            $table->string('status')->default('not_started');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('teacher_note')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'milestone_id']);
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('milestone_id')->references('id')->on('hifz_milestones')->cascadeOnDelete();
            $table->index('status');
        });

        Schema::create('recitation_evaluations', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('student_id', 26);
            $table->char('teacher_id', 26);
            $table->char('milestone_id', 26)->nullable();
            $table->char('live_stream_id', 26)->nullable();
            $table->unsignedTinyInteger('memorization');
            $table->unsignedTinyInteger('tajwid');
            $table->unsignedTinyInteger('fluency');
            $table->text('comments')->nullable();
            $table->timestamp('evaluated_at');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('milestone_id')->references('id')->on('hifz_milestones')->nullOnDelete();
            $table->index('evaluated_at');
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('student_id', 26);
            $table->char('milestone_id', 26);
            $table->char('progress_id', 26)->unique();
            $table->string('verification_code', 16)->unique();
            $table->jsonb('level_label_i18n');
            $table->timestamp('issued_at');
            $table->string('document_path')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('milestone_id')->references('id')->on('hifz_milestones')->cascadeOnDelete();
            $table->foreign('progress_id')->references('id')->on('student_progress')->cascadeOnDelete();
        });

        Schema::create('ijazas', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('teacher_id', 26);
            $table->char('student_id', 26);
            $table->jsonb('scope_i18n');
            $table->jsonb('sanad_i18n');
            $table->timestamp('signed_at');
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('quizzes', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->string('slug')->unique();
            $table->string('topic')->comment('tajwid, vocabulary');
            $table->jsonb('title_i18n');
            $table->jsonb('questions');
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('quiz_id', 26);
            $table->char('user_id', 26);
            $table->jsonb('answers');
            $table->unsignedTinyInteger('score');
            $table->unsignedTinyInteger('total');
            $table->boolean('passed');
            $table->timestamp('completed_at');
            $table->timestamps();

            $table->foreign('quiz_id')->references('id')->on('quizzes')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('quizzes');
        Schema::dropIfExists('ijazas');
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('recitation_evaluations');
        Schema::dropIfExists('student_progress');
        Schema::dropIfExists('badges');
        Schema::dropIfExists('hifz_milestones');
    }
};
