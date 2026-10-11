<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_channels', function (Blueprint $table) {
            $table->string('rtmp_ingest_url')->nullable()->change();
            $table->string('whep_url')->nullable()->change();
            $table->string('hls_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('live_channels', function (Blueprint $table) {
            $table->string('rtmp_ingest_url')->nullable(false)->change();
            $table->string('whep_url')->nullable(false)->change();
            $table->string('hls_url')->nullable(false)->change();
        });
    }
};
