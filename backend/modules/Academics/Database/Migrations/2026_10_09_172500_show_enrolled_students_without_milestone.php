<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE student_progress ALTER COLUMN milestone_id DROP NOT NULL');

        $studentIds = DB::table('enrollments')->distinct()->pluck('user_id');

        foreach ($studentIds as $studentId) {
            $hasProgress = DB::table('student_progress')->where('student_id', $studentId)->exists();
            if ($hasProgress) {
                continue;
            }

            DB::table('student_progress')->insert([
                'id' => (string) Str::ulid(),
                'student_id' => $studentId,
                'milestone_id' => null,
                'status' => 'not_started',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('student_progress')->whereNull('milestone_id')->delete();
        DB::statement('ALTER TABLE student_progress ALTER COLUMN milestone_id SET NOT NULL');
    }
};
