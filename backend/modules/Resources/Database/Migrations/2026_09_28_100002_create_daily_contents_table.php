<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_contents', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->date('display_date');
            $table->string('type')->comment('verse, hadith');
            $table->text('arabic_text');
            $table->jsonb('translation_i18n');
            $table->jsonb('commentary_i18n')->nullable();
            $table->string('source');
            $table->string('reference')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->unique(['display_date', 'type']);
            $table->index(['type', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_contents');
    }
};
