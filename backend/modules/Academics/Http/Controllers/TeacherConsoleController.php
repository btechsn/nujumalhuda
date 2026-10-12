<?php

declare(strict_types=1);

namespace Modules\Academics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Academics\Models\RecitationEvaluation;
use Modules\Core\Models\AuditLog;
use Modules\Core\Models\User;
use Modules\Education\Models\Enrollment;
use Modules\Education\Models\Teacher;

class TeacherConsoleController extends Controller
{
    public function students(Request $request)
    {
        $this->teacherOrAbort($request);

        $ids = Enrollment::query()
            ->whereIn('status', ['approved', 'active', 'completed'])
            ->pluck('user_id')
            ->unique()
            ->values();

        $students = User::query()
            ->whereIn('id', $ids)
            ->whereNull('anonymized_at')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->fullName(),
            ])
            ->filter(fn (array $row) => $row['name'] !== '')
            ->values();

        return response()->json(['data' => $students]);
    }

    public function store(Request $request)
    {
        $teacher = $this->teacherOrAbort($request);
        $data = $request->validate([
            'student_id' => ['required', 'string', 'size:26'],
            'milestone_id' => ['nullable', 'string', 'size:26', 'exists:hifz_milestones,id'],
            'memorization' => ['required', 'integer', 'between:1,5'],
            'tajwid' => ['required', 'integer', 'between:1,5'],
            'fluency' => ['required', 'integer', 'between:1,5'],
            'comments' => ['nullable', 'string', 'max:1000'],
        ]);

        $student = User::query()->whereKey($data['student_id'])->whereNull('anonymized_at')->first();
        $enrolled = $student && Enrollment::query()
            ->where('user_id', $student->id)
            ->whereIn('status', ['approved', 'active', 'completed'])
            ->exists();

        if (! $enrolled) {
            return response()->json([
                'message' => 'Cet élève ne peut pas être évalué.',
            ], 422);
        }

        $evaluation = RecitationEvaluation::query()->create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'milestone_id' => $data['milestone_id'] ?? null,
            'memorization' => $data['memorization'],
            'tajwid' => $data['tajwid'],
            'fluency' => $data['fluency'],
            'comments' => $data['comments'] ?? null,
            'evaluated_at' => now(),
        ]);

        AuditLog::log('recitation.evaluated', $evaluation, context: [
            'memorization' => $evaluation->memorization,
            'tajwid' => $evaluation->tajwid,
            'fluency' => $evaluation->fluency,
        ]);

        return response()->json([
            'id' => $evaluation->id,
            'student' => ['id' => $student->id, 'name' => $student->fullName()],
            'memorization' => $evaluation->memorization,
            'tajwid' => $evaluation->tajwid,
            'fluency' => $evaluation->fluency,
            'evaluated_at' => $evaluation->evaluated_at?->toIso8601String(),
        ], 201);
    }

    private function teacherOrAbort(Request $request): User
    {
        $user = $request->user();
        $isTeacher = Teacher::query()->whereKey($user->id)->exists()
            || $user->memberships()->where('status', 'active')->whereHas('role', fn ($query) => $query->where('name', 'teacher'))->exists();

        if (! $isTeacher) {
            abort(403, 'Réservé aux enseignants.');
        }

        return $user;
    }
}
