<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cahier des charges, §8.3, point 2 : « Diffusion des récitations par
 * défaut restreinte aux enseignants et aux tuteurs de l'élève. Le passage
 * en public est une action explicite. »
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recitation_sessions', function (Blueprint $table) {
            $table->string('visibility')->default('restricted')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('recitation_sessions', function (Blueprint $table) {
            $table->dropColumn('visibility');
        });
    }
};
