<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->string('event_name')->nullable()->after('caption_i18n');
            $table->string('thumbnail_url')->nullable()->after('media_url');
            $table->index('kind');
            $table->index('event_name');
        });
    }

    public function down(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->dropIndex(['kind']);
            $table->dropIndex(['event_name']);
            $table->dropColumn(['event_name', 'thumbnail_url']);
        });
    }
};
