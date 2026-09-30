<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mosque_events', function (Blueprint $table) {
            $table->string('youtube_url')->nullable()->after('image_media_id');
        });
    }

    public function down(): void
    {
        Schema::table('mosque_events', function (Blueprint $table) {
            $table->dropColumn('youtube_url');
        });
    }
};
