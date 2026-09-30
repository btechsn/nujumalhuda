<?php

namespace Modules\Live\Services;

use Modules\Core\Contracts\RecitationScheduler;
use Modules\Live\Models\RecitationSession;

class LiveRecitationScheduler implements RecitationScheduler
{
    public function onMilestoneCompleted(string $studentId, string $milestoneId, ?string $teacherId, string $title): void
    {
        $exists = RecitationSession::query()
            ->where('trigger', 'milestone_completed')
            ->where('student_id', $studentId)
            ->where('milestone_id', $milestoneId)
            ->exists();

        if ($exists) {
            return;
        }

        RecitationSession::create([
            'trigger' => 'milestone_completed',
            'student_id' => $studentId,
            'teacher_id' => $teacherId,
            'milestone_id' => $milestoneId,
            'title' => $title,
            'starts_at' => now(),
            'status' => 'scheduled',
        ]);
    }
}
