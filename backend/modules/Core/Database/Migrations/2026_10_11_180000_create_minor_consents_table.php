<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cahier des charges, §8.3, point 1 : « Drapeau de consentement sur la
 * fiche élève, distinct par usage : image en galerie, voix en récitation
 * diffusée, nom en attestation publique. Sans consentement enregistré, la
 * publication est techniquement impossible. »
 *
 * Une ligne par élève (tuteuré), remplie par le secrétariat à partir d'un
 * consentement parental écrit (papier), pas par un parcours de cases à
 * cocher en ligne.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('minor_consents', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('user_id', 26)->unique();

            $table->boolean('gallery_image_consent')->default(false);
            $table->boolean('recitation_audio_consent')->default(false);
            $table->boolean('public_name_consent')->default(false);

            $table->char('consented_by', 26)->nullable();
            $table->timestamp('consented_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('consented_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('minor_consents');
    }
};
