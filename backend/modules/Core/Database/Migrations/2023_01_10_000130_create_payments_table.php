<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            
            $table->string('payable_type');
            $table->char('payable_id', 26);
            
            $table->char('user_id', 26);
            $table->char('organization_id', 26)->nullable();
            
            $table->string('method', 30);
            $table->string('status', 20)->default('pending')->index();
            $table->string('currency', 3)->default('XOF');
            $table->unsignedBigInteger('amount_minor');
            
            $table->string('gateway_transaction_id')->nullable()->unique();
            $table->jsonb('gateway_response')->nullable();
            $table->jsonb('metadata')->nullable();
            
            $table->timestampTz('completed_at')->nullable();
            $table->timestampTz('failed_at')->nullable();
            $table->timestampTz('refunded_at')->nullable();
            $table->timestampsTz();
            
            $table->index(['payable_type', 'payable_id']);
            $table->index(['user_id', 'status']);
            $table->index('organization_id');
            
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
            
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->nullOnDelete();
            
            $table->check('method IN (\'wave\', \'orange_money\', \'cash\', \'bank_transfer\')');
            $table->check('status IN (\'pending\', \'processing\', \'completed\', \'failed\', \'cancelled\', \'refunded\')');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
