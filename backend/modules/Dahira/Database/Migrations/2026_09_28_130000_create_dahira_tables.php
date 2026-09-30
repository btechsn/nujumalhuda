<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dahira_groups', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('organization_id', 26)->unique();
            $table->jsonb('name_i18n');
            $table->jsonb('description_i18n')->nullable();
            $table->date('founded_on')->nullable();
            $table->unsignedTinyInteger('meeting_weekday')->nullable();
            $table->time('meeting_time')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
        });

        Schema::create('contribution_plans', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('dahira_group_id', 26);
            $table->jsonb('name_i18n');
            $table->unsignedBigInteger('amount_minor');
            $table->string('currency', 3)->default('XOF');
            $table->string('frequency')->default('monthly');
            $table->unsignedTinyInteger('due_day')->default(5);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('dahira_group_id')->references('id')->on('dahira_groups')->cascadeOnDelete();
        });

        Schema::create('contribution_schedules', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('plan_id', 26);
            $table->char('membership_id', 26);
            $table->date('period_start');
            $table->date('due_on');
            $table->unsignedBigInteger('amount_minor');
            $table->string('status')->default('due');
            $table->timestamp('reminded_at')->nullable();
            $table->timestamps();

            $table->unique(['plan_id', 'membership_id', 'period_start']);
            $table->foreign('plan_id')->references('id')->on('contribution_plans')->cascadeOnDelete();
            $table->foreign('membership_id')->references('id')->on('memberships')->cascadeOnDelete();
            $table->index(['status', 'due_on']);
        });

        Schema::create('contributions', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('dahira_group_id', 26);
            $table->char('membership_id', 26);
            $table->char('schedule_id', 26)->nullable();
            $table->char('payment_id', 26)->nullable();
            $table->char('recorded_by', 26);
            $table->unsignedBigInteger('amount_minor');
            $table->string('currency', 3)->default('XOF');
            $table->date('paid_on');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('dahira_group_id')->references('id')->on('dahira_groups')->cascadeOnDelete();
            $table->foreign('membership_id')->references('id')->on('memberships')->cascadeOnDelete();
            $table->foreign('schedule_id')->references('id')->on('contribution_schedules')->nullOnDelete();
            $table->foreign('payment_id')->references('id')->on('payments')->nullOnDelete();
            $table->foreign('recorded_by')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('meetings', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('dahira_group_id', 26);
            $table->string('title');
            $table->timestamp('starts_at');
            $table->string('location')->nullable();
            $table->text('agenda')->nullable();
            $table->timestamp('convened_at')->nullable();
            $table->timestamps();

            $table->foreign('dahira_group_id')->references('id')->on('dahira_groups')->cascadeOnDelete();
            $table->index('starts_at');
        });

        Schema::create('meeting_attendances', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('meeting_id', 26);
            $table->char('membership_id', 26);
            $table->string('status')->default('absent');
            $table->timestamps();

            $table->unique(['meeting_id', 'membership_id']);
            $table->foreign('meeting_id')->references('id')->on('meetings')->cascadeOnDelete();
            $table->foreign('membership_id')->references('id')->on('memberships')->cascadeOnDelete();
        });

        Schema::create('treasury_entries', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('dahira_group_id', 26);
            $table->string('direction');
            $table->string('category');
            $table->unsignedBigInteger('amount_minor');
            $table->string('currency', 3)->default('XOF');
            $table->string('label');
            $table->date('occurred_on');
            $table->char('contribution_id', 26)->nullable();
            $table->char('payment_id', 26)->nullable();
            $table->char('recorded_by', 26);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('dahira_group_id')->references('id')->on('dahira_groups')->cascadeOnDelete();
            $table->foreign('contribution_id')->references('id')->on('contributions')->nullOnDelete();
            $table->foreign('payment_id')->references('id')->on('payments')->nullOnDelete();
            $table->foreign('recorded_by')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['dahira_group_id', 'occurred_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treasury_entries');
        Schema::dropIfExists('meeting_attendances');
        Schema::dropIfExists('meetings');
        Schema::dropIfExists('contributions');
        Schema::dropIfExists('contribution_schedules');
        Schema::dropIfExists('contribution_plans');
        Schema::dropIfExists('dahira_groups');
    }
};
