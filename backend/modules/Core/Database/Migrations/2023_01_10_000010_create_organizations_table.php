<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('parent_id', 26)->nullable()->index();
            
            $table->string('type', 20)->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->jsonb('description')->nullable();
            
            $table->string('logo')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 2)->default('SN');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            
            $table->string('website')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->jsonb('settings')->nullable();
            
            $table->timestampsTz();
            
            $table->foreign('parent_id')
                ->references('id')
                ->on('organizations')
                ->nullOnDelete();
            
            $table->check('type IN (\'center\', \'mosque\', \'dahira\', \'zawiya\')');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
