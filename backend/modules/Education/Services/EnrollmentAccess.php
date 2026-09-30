<?php

namespace Modules\Education\Services;

use Modules\Core\Models\Guardianship;
use Modules\Core\Models\User;
use Modules\Education\Models\Enrollment;

class EnrollmentAccess
{
    public function allows(?User $user, Enrollment $enrollment): bool
    {
        if (!$user) {
            return false;
        }

        if ($enrollment->user_id === $user->id || $user->isStaff()) {
            return true;
        }

        return Guardianship::query()
            ->where('guardian_id', $user->id)
            ->where('ward_id', $enrollment->user_id)
            ->exists();
    }
}
