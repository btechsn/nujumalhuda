<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hijri_observances', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->unsignedSmallInteger('hijri_year');
            $table->string('kind', 32);
            $table->date('gregorian_date');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['hijri_year', 'kind']);
            $table->index('gregorian_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hijri_observances');
    }
};
