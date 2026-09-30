<?php

namespace Modules\Community\Services;

use Modules\Community\Models\Discussion;
use Modules\Core\Models\Guardianship;
use Modules\Core\Models\User;
use Modules\Education\Models\Enrollment;
use Modules\Education\Models\Promotion;

class DiscussionAccess
{
    public function canAccess(User $user, Discussion $discussion): bool
    {
        if ($discussion->discussable_type !== Promotion::class) {
            return false;
        }

        $promotion = Promotion::find($discussion->discussable_id);
        if (!$promotion) {
            return false;
        }

        if ($promotion->main_teacher_id === $user->id) {
            return true;
        }

        $enrolled = Enrollment::query()
            ->where('promotion_id', $promotion->id)
            ->whereIn('status', ['approved', 'active', 'completed'])
            ->pluck('user_id');

        if ($enrolled->contains($user->id)) {
            return true;
        }

        return Guardianship::query()
            ->where('guardian_id', $user->id)
            ->whereIn('ward_id', $enrolled)
            ->exists();
    }

    public function isModerator(User $user, Discussion $discussion): bool
    {
        if ($discussion->discussable_type !== Promotion::class) {
            return false;
        }

        $promotion = Promotion::find($discussion->discussable_id);

        return $promotion && $promotion->main_teacher_id === $user->id;
    }
}
