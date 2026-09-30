<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email')->unique()->nullable();
            $table->string('phone', 20)->unique()->nullable();
            $table->string('password');
            
            $table->string('locale', 2)->default('fr');
            $table->string('timezone', 50)->default('Africa/Dakar');
            $table->string('avatar')->nullable();
            
            $table->timestampTz('email_verified_at')->nullable();
            $table->timestampTz('phone_verified_at')->nullable();
            $table->timestampTz('last_login_at')->nullable();
            
            $table->rememberToken();
            $table->timestampsTz();
            
            $table->index(['email', 'phone']);
            $table->check('email IS NOT NULL OR phone IS NOT NULL');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
