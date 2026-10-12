<?php

namespace Modules\Live\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\AuditLog;
use Modules\Core\Models\User;
use Modules\Live\Models\RecitationSession;

class RecitationSessionController extends Controller
{
    public function index()
    {
        // Cahier des charges, §8.3, point 2 : une séance de récitation reste
        // réservée aux enseignants et tuteurs tant qu'elle n'a pas été
        // explicitement rendue publique (et ça suppose un consentement
        // enregistré, voir Filament\Resources\RecitationSessionResource).
        return RecitationSession::query()
            ->whereIn('status', ['scheduled', 'live'])
            ->where('visibility', 'public')
            ->orderBy('starts_at')
            ->get();
    }

    /**
     * Séances de l'enseignant connecté. La diffusion RTMP n'est pas ouverte ici.
     */
    public function mine(Request $request)
    {
        $teacher = $this->teacherOrAbort($request);

        $sessions = RecitationSession::query()
            ->with('student:id,first_name,last_name,anonymized_at')
            ->where('teacher_id', $teacher->id)
            ->latest('starts_at')
            ->limit(20)
            ->get()
            ->map(fn (RecitationSession $session) => $this->payload($session));

        return response()->json(['data' => $sessions]);
    }

    public function store(Request $request)
    {
        $teacher = $this->teacherOrAbort($request);
        $data = $request->validate([
            'title' => 'required|string|max:180',
            'student_id' => 'required|string|size:26',
        ]);

        if (! $this->studentIsListenable($data['student_id'])) {
            return response()->json([
                'message' => 'Cet élève ne peut pas être inscrit à une séance.',
            ], 422);
        }

        $session = RecitationSession::create([
            'title' => $data['title'],
            'student_id' => $data['student_id'],
            'trigger' => 'scheduled',
            'teacher_id' => $teacher->id,
            'starts_at' => now(),
            'status' => 'live',
            'visibility' => 'restricted',
        ]);

        AuditLog::log('recitation.session_started', $session, context: [
            'visibility' => 'restricted',
        ]);

        return response()->json($this->payload($session->load('student:id,first_name,last_name,anonymized_at')), 201);
    }

    public function start(Request $request, string $id)
    {
        $teacher = $this->teacherOrAbort($request);
        $session = RecitationSession::query()
            ->where('teacher_id', $teacher->id)
            ->findOrFail($id);

        if ($session->status === 'ended') {
            return response()->json(['message' => 'Cette séance est déjà terminée.'], 422);
        }

        if ($session->status !== 'live') {
            $session->update([
                'status' => 'live',
                'starts_at' => $session->starts_at ?? now(),
                'visibility' => 'restricted',
            ]);
            AuditLog::log('recitation.session_started', $session, context: [
                'visibility' => 'restricted',
            ]);
        }

        return response()->json($this->payload($session->fresh()->load('student:id,first_name,last_name,anonymized_at')));
    }

    private function teacherOrAbort(Request $request): User
    {
        $user = $request->user();
        $isTeacher = DB::table('teachers')->where('id', $user->id)->whereNull('deleted_at')->exists()
            || $user->memberships()->where('status', 'active')->whereHas('role', fn ($query) => $query->where('name', 'teacher'))->exists();

        if (! $isTeacher) {
            abort(403, 'Réservé aux enseignants.');
        }

        return $user;
    }

    private function studentIsListenable(string $studentId): bool
    {
        $student = User::query()->whereKey($studentId)->whereNull('anonymized_at')->first();
        if (! $student) {
            return false;
        }

        return DB::table('enrollments')
            ->where('user_id', $student->id)
            ->whereIn('status', ['approved', 'active', 'completed'])
            ->whereNull('deleted_at')
            ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(RecitationSession $session): array
    {
        $student = $session->student;

        return [
            'id' => $session->id,
            'title' => $session->title,
            'status' => $session->status,
            'visibility' => $session->visibility,
            'starts_at' => $session->starts_at?->toIso8601String(),
            'student' => $student && $student->anonymized_at === null ? [
                'id' => $student->id,
                'name' => $student->fullName(),
            ] : null,
        ];
    }
}
