<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->string('media_path')->nullable()->after('media_url');
            $table->string('thumbnail_path')->nullable()->after('thumbnail_url');
            $table->string('media_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->dropColumn(['media_path', 'thumbnail_path']);
            $table->string('media_url')->nullable(false)->change();
        });
    }
};
