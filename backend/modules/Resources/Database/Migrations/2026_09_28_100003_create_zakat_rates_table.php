<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zakat_rates', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->string('madhhab')->default('maliki');
            $table->string('nisab_basis')->comment('gold, silver');
            $table->decimal('gold_nisab_grams', 8, 2)->default(85);
            $table->decimal('silver_nisab_grams', 8, 2)->default(595);
            $table->unsignedBigInteger('gold_price_per_gram_minor');
            $table->unsignedBigInteger('silver_price_per_gram_minor');
            $table->string('currency', 3)->default('XOF');
            $table->unsignedTinyInteger('rate_numerator')->default(1);
            $table->unsignedTinyInteger('rate_denominator')->default(40);
            $table->jsonb('source_i18n');
            $table->date('prices_as_of');
            $table->boolean('prices_are_indicative')->default(true);
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->index('is_current');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zakat_rates');
    }
};
