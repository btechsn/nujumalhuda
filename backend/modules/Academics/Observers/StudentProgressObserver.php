<?php

namespace Modules\Academics\Observers;

use Modules\Academics\Models\StudentProgress;
use Modules\Academics\Services\CertificateIssuer;
use Modules\Core\Contracts\RecitationScheduler;

class StudentProgressObserver
{
    public function created(StudentProgress $progress): void
    {
        $this->issueIfCompleted($progress);
    }

    public function updated(StudentProgress $progress): void
    {
        if ($progress->wasChanged('status')) {
            $this->issueIfCompleted($progress);
        }
    }

    private function issueIfCompleted(StudentProgress $progress): void
    {
        if ($progress->status !== 'completed' || ! $progress->milestone_id) {
            return;
        }

        if (! $progress->completed_at) {
            $progress->forceFill(['completed_at' => now()])->saveQuietly();
        }

        app(CertificateIssuer::class)->issueFor($progress->fresh());

        if (app()->bound(RecitationScheduler::class)) {
            $fresh = $progress->fresh('milestone');
            app(RecitationScheduler::class)->onMilestoneCompleted(
                $fresh->student_id,
                $fresh->milestone_id,
                null,
                $fresh->milestone->title_i18n['fr'] ?? 'Récitation',
            );
        }
    }
}
