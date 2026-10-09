<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_topics', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->string('slug')->unique();
            $table->jsonb('name_i18n');
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        $now = now();
        $topics = [
            ['tajwid', ['fr' => 'Tajwid', 'en' => 'Tajweed', 'ar' => 'التجويد'], 1],
            ['vocabulary', ['fr' => 'Vocabulaire', 'en' => 'Vocabulary', 'ar' => 'المفردات'], 2],
            ['coran', ['fr' => 'Coran', 'en' => 'Qur\'an', 'ar' => 'القرآن'], 3],
            ['arabe', ['fr' => 'Arabe', 'en' => 'Arabic', 'ar' => 'العربية'], 4],
            ['fiqh', ['fr' => 'Fiqh et foi', 'en' => 'Fiqh and faith', 'ar' => 'الفقه والعقيدة'], 5],
            ['baye_niasse', ['fr' => 'Baye Niasse', 'en' => 'Baye Niasse', 'ar' => 'الشيخ إبراهيم نياس'], 6],
        ];

        foreach ($topics as [$slug, $name, $sort]) {
            DB::table('quiz_topics')->insert([
                'id' => (string) Str::ulid(),
                'slug' => $slug,
                'name_i18n' => json_encode($name, JSON_UNESCAPED_UNICODE),
                'sort' => $sort,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_topics');
    }
};
