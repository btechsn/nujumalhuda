<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE article_comments ALTER COLUMN user_id DROP NOT NULL');

        Schema::table('article_comments', function (Blueprint $table) {
            $table->string('author_name')->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('article_comments', function (Blueprint $table) {
            $table->dropColumn('author_name');
        });

        DB::statement('ALTER TABLE article_comments ALTER COLUMN user_id SET NOT NULL');
    }
};
