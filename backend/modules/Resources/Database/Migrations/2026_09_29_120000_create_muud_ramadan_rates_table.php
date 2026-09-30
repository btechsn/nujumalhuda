<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('muud_ramadan_rates', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->unsignedSmallInteger('hijri_year');
            $table->string('madhhab')->default('maliki');
            $table->unsignedBigInteger('amount_per_person_minor');
            $table->string('currency', 3)->default('XOF');
            $table->string('staple')->default('rice');
            $table->decimal('sa_grams', 8, 2)->default(2400);
            $table->jsonb('label_i18n');
            $table->jsonb('source_i18n');
            $table->date('prices_as_of');
            $table->boolean('prices_are_indicative')->default(true);
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->index('is_current');
            $table->index('hijri_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('muud_ramadan_rates');
    }
};
