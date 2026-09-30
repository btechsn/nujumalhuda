<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('article_comments', function (Blueprint $table) {
            $table->unsignedTinyInteger('spam_score')->default(0)->after('reports_count');
            $table->json('spam_flags')->nullable()->after('spam_score');
            $table->string('ip_address', 45)->nullable()->after('spam_flags');
            $table->text('user_agent')->nullable()->after('ip_address');

            $table->index('spam_score');
        });
    }

    public function down(): void
    {
        Schema::table('article_comments', function (Blueprint $table) {
            $table->dropIndex(['spam_score']);
            $table->dropColumn(['spam_score', 'spam_flags', 'ip_address', 'user_agent']);
        });
    }
};
