<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cahier des charges, §8.3, point 1 : quand un média identifie un élève
 * précis, sa publication devient techniquement impossible sans le
 * consentement « photo en galerie » enregistré sur sa fiche.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->char('student_id', 26)->nullable()->after('kind');
            $table->foreign('student_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');
        });
    }
};
