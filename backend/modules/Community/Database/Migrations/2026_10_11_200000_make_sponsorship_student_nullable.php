<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sponsorships', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
        });

        DB::statement('ALTER TABLE sponsorships ALTER COLUMN student_id DROP NOT NULL');

        Schema::table('sponsorships', function (Blueprint $table) {
            $table->foreign('student_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        DB::table('sponsorships')->whereNull('student_id')->delete();

        Schema::table('sponsorships', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
        });

        DB::statement('ALTER TABLE sponsorships ALTER COLUMN student_id SET NOT NULL');

        Schema::table('sponsorships', function (Blueprint $table) {
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
