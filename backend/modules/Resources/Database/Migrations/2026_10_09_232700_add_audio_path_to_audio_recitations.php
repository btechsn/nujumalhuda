<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audio_recitations', function (Blueprint $table) {
            $table->string('audio_path')->nullable()->after('audio_url');
            $table->string('audio_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('audio_recitations', function (Blueprint $table) {
            $table->dropColumn('audio_path');
            $table->string('audio_url')->nullable(false)->change();
        });
    }
};
