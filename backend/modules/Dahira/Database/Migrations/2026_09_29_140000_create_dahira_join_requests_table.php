<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dahira_join_requests', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('dahira_group_id', 26);
            $table->string('first_name', 60);
            $table->string('last_name', 60);
            $table->string('phone', 20);
            $table->text('message')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->char('reviewed_by', 26)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->char('membership_id', 26)->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();

            $table->foreign('dahira_group_id')->references('id')->on('dahira_groups')->cascadeOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('membership_id')->references('id')->on('memberships')->nullOnDelete();
            $table->index(['dahira_group_id', 'phone']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dahira_join_requests');
    }
};
