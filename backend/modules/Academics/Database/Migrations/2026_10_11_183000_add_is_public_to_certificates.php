<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cahier des charges, §8.3, point 1 : une attestation de niveau porte le
 * nom de l'élève. Comme pour l'ijaza, elle reste privée par défaut et sa
 * publication exige le consentement « nom public ».
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->boolean('is_public')->default(false)->after('level_label_i18n');
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });
    }
};
