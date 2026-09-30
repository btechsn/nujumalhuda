<?php

namespace Modules\Dahira\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Core\Enums\MembershipStatus;
use Modules\Core\Models\Membership;
use Modules\Core\Models\Role;
use Modules\Core\Models\User;
use Modules\Dahira\Models\DahiraJoinRequest;
use RuntimeException;

class JoinRequestService
{
    public function approve(DahiraJoinRequest $request, User $reviewer, ?string $note = null): DahiraJoinRequest
    {
        if ($request->status !== 'pending') {
            throw new RuntimeException('Cette demande a déjà été traitée.');
        }

        $group = $request->group()->with('organization')->firstOrFail();
        $role = Role::query()->where('name', 'dahira_member')->firstOrFail();

        return DB::transaction(function () use ($request, $reviewer, $note, $group, $role) {
            $user = User::query()->where('phone', $request->phone)->first();

            if (!$user) {
                $user = User::query()->create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'phone' => $request->phone,
                    'password' => Str::password(32),
                    'locale' => 'fr',
                    'timezone' => 'Africa/Dakar',
                ]);
            } else {
                $user->fill([
                    'first_name' => $user->first_name ?: $request->first_name,
                    'last_name' => $user->last_name ?: $request->last_name,
                ])->save();
            }

            $membership = Membership::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'organization_id' => $group->organization_id,
                    'role_id' => $role->id,
                ],
                [
                    'status' => MembershipStatus::ACTIVE,
                    'joined_at' => now(),
                    'notes' => $note,
                ]
            );

            $request->update([
                'status' => 'approved',
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'membership_id' => $membership->id,
                'review_note' => $note,
            ]);

            return $request->fresh(['group', 'membership', 'reviewer']);
        });
    }

    public function reject(DahiraJoinRequest $request, User $reviewer, ?string $note = null): DahiraJoinRequest
    {
        if ($request->status !== 'pending') {
            throw new RuntimeException('Cette demande a déjà été traitée.');
        }

        $request->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_note' => $note,
        ]);

        return $request->fresh(['group', 'reviewer']);
    }
}
