<?php

namespace Modules\Academics\Services;

use Modules\Core\Models\Guardianship;
use Modules\Core\Models\User;

class ProgressAccess
{
    public function canView(User $viewer, string $studentId): bool
    {
        if ($viewer->id === $studentId) {
            return true;
        }

        return Guardianship::query()
            ->where('guardian_id', $viewer->id)
            ->where('ward_id', $studentId)
            ->where('can_view_progress', true)
            ->exists();
    }
}
