<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ijazas', function (Blueprint $table) {
            $table->string('verification_code', 16)->nullable()->unique();
            $table->string('document_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('ijazas', function (Blueprint $table) {
            $table->dropColumn(['verification_code', 'document_path']);
        });
    }
};
