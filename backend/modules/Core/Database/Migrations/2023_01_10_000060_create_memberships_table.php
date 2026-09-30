<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            
            $table->char('user_id', 26);
            $table->char('organization_id', 26);
            $table->char('role_id', 26);
            
            $table->string('status', 20)->default('active')->index();
            $table->timestampTz('joined_at')->useCurrent();
            $table->timestampTz('expires_at')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestampsTz();
            
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
            
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->restrictOnDelete();
            
            $table->unique(['user_id', 'organization_id', 'role_id'], 'unique_user_org_role');
            $table->index(['organization_id', 'status']);
            
            $table->check('status IN (\'pending\', \'active\', \'suspended\', \'expired\', \'revoked\')');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
