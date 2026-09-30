<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->string('first_name', 60);
            $table->string('last_name', 60);
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('subject', 160);
            $table->text('message');
            $table->string('locale', 2)->default('fr');
            $table->string('status', 20)->default('new')->index();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
