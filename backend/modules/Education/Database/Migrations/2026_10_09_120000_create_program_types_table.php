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
        Schema::create('program_types', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->string('slug')->unique();
            $table->jsonb('name_i18n');
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        $now = now();
        $types = [
            ['coran', ['fr' => 'Coran', 'en' => 'Quran', 'ar' => 'القرآن'], 1],
            ['arabe', ['fr' => 'Arabe', 'en' => 'Arabic', 'ar' => 'العربية'], 2],
            ['baye_niasse', ['fr' => 'Baye Niasse', 'en' => 'Baye Niasse', 'ar' => 'باي نياس'], 3],
            ['sunnite', ['fr' => 'Sciences islamiques', 'en' => 'Islamic sciences', 'ar' => 'العلوم الإسلامية'], 4],
        ];

        foreach ($types as [$slug, $name, $sort]) {
            DB::table('program_types')->insert([
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
        Schema::dropIfExists('program_types');
    }
};
