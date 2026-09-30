<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('user_id', 26)->nullable();
            $table->string('donor_name')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->unsignedBigInteger('amount_minor');
            $table->string('currency', 3)->default('XOF');
            $table->string('status')->default('pending');
            $table->text('message')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['status', 'paid_at']);
        });

        Schema::create('sponsorships', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('sponsor_id', 26);
            $table->char('student_id', 26);
            $table->unsignedBigInteger('amount_minor');
            $table->string('currency', 3)->default('XOF');
            $table->string('frequency')->default('monthly');
            $table->string('status')->default('active');
            $table->timestamps();

            $table->foreign('sponsor_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('user_id', 26)->nullable();
            $table->string('author_name');
            $table->string('relation')->default('parent');
            $table->jsonb('content_i18n');
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index('status');
        });

        Schema::create('gallery_items', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->string('kind')->default('photo');
            $table->jsonb('caption_i18n')->nullable();
            $table->string('media_url');
            $table->date('taken_on')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('asker_id', 26);
            $table->char('teacher_id', 26)->nullable();
            $table->jsonb('question_i18n');
            $table->jsonb('answer_i18n')->nullable();
            $table->string('status')->default('pending');
            $table->boolean('is_public')->default(false);
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->foreign('asker_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('teacher_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['status', 'is_public']);
        });

        Schema::create('events', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->jsonb('title_i18n');
            $table->jsonb('description_i18n')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->string('location')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('starts_at');
        });

        Schema::create('event_registrations', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('event_id', 26);
            $table->char('user_id', 26)->nullable();
            $table->string('full_name');
            $table->string('phone', 20);
            $table->string('confirmation_code', 12)->unique();
            $table->string('status')->default('confirmed');
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->unique(['event_id', 'phone']);
        });

        Schema::create('sms_digest_subscribers', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->string('phone', 20)->unique();
            $table->string('locale', 5)->default('fr');
            $table->timestamp('consented_at');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_prepared_at')->nullable();
            $table->timestamps();
        });

        Schema::create('discussions', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->string('discussable_type');
            $table->char('discussable_id', 26);
            $table->char('created_by', 26);
            $table->string('title');
            $table->timestamps();

            $table->index(['discussable_type', 'discussable_id']);
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('discussion_messages', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('discussion_id', 26);
            $table->char('user_id', 26);
            $table->text('body');
            $table->string('status')->default('visible');
            $table->timestamps();

            $table->foreign('discussion_id')->references('id')->on('discussions')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['discussion_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discussion_messages');
        Schema::dropIfExists('discussions');
        Schema::dropIfExists('sms_digest_subscribers');
        Schema::dropIfExists('event_registrations');
        Schema::dropIfExists('events');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('gallery_items');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('sponsorships');
        Schema::dropIfExists('donations');
    }
};
