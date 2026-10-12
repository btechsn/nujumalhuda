<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cahier des charges, §8.3, point 4 : droit à l'effacement outillé par une
 * procédure d'anonymisation. On marque la date plutôt que de supprimer la
 * ligne, pour ne pas casser les enregistrements qui y font référence
 * (inscriptions, attestations, écritures de trésorerie du dahira, etc.).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('anonymized_at')->nullable()->after('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('anonymized_at');
        });
    }
};
