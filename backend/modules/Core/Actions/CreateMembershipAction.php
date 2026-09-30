<?php

declare(strict_types=1);

namespace Modules\Core\Actions;

use Modules\Core\Data\MembershipData;
use Modules\Core\Events\MembershipCreated;
use Modules\Core\Models\Membership;

class CreateMembershipAction
{
    public function execute(MembershipData $data): Membership
    {
        $membership = Membership::create([
            'user_id' => $data->user_id,
            'organization_id' => $data->organization_id,
            'role_id' => $data->role_id,
            'status' => $data->status,
            'joined_at' => $data->joined_at ?? now(),
            'expires_at' => $data->expires_at,
            'notes' => $data->notes,
        ]);

        event(new MembershipCreated($membership));

        return $membership;
    }
}
