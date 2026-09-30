<?php

namespace Modules\Dahira\Services;

use Modules\Core\Models\Membership;
use Modules\Core\Models\User;
use Modules\Dahira\Models\DahiraGroup;

class DahiraAccess
{
    public const OFFICER_ROLES = ['dahira_muqaddam', 'dahira_secretary'];

    public const TREASURER_ROLES = ['dahira_muqaddam', 'dahira_treasurer'];

    public function membership(User $user, DahiraGroup $group): ?Membership
    {
        return Membership::query()
            ->where('user_id', $user->id)
            ->where('organization_id', $group->organization_id)
            ->where('status', 'active')
            ->with('role')
            ->first();
    }

    public function isMember(User $user, DahiraGroup $group): bool
    {
        $membership = $this->membership($user, $group);

        return $membership !== null && $membership->isActive();
    }

    public function isOfficer(User $user, DahiraGroup $group): bool
    {
        $membership = $this->membership($user, $group);

        return $membership && in_array($membership->role?->name, self::OFFICER_ROLES, true);
    }

    public function isTreasurer(User $user, DahiraGroup $group): bool
    {
        $membership = $this->membership($user, $group);

        return $membership && in_array($membership->role?->name, self::TREASURER_ROLES, true);
    }
}
