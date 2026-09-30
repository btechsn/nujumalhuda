<?php

namespace Modules\Dahira\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Models\Membership;
use Modules\Dahira\Models\DahiraGroup;
use Modules\Dahira\Models\DahiraJoinRequest;

class DahiraFeedController extends Controller
{
    public function board()
    {
        $locale = app()->getLocale();

        $groups = DahiraGroup::query()
            ->with(['plans' => fn ($q) => $q->where('is_active', true)])
            ->where('is_active', true)
            ->orderBy('founded_on')
            ->orderBy('name_i18n->fr')
            ->limit(120)
            ->get()
            ->map(function (DahiraGroup $group) use ($locale) {
                $members = Membership::query()
                    ->where('organization_id', $group->organization_id)
                    ->active()
                    ->count();

                $plan = $group->plans->first();

                return [
                    'id' => $group->id,
                    'name' => $group->name_i18n[$locale] ?? $group->name_i18n['fr'] ?? '',
                    'description' => $group->description_i18n[$locale] ?? $group->description_i18n['fr'] ?? '',
                    'location' => $group->location,
                    'members' => $members,
                    'meeting_weekday' => $group->meeting_weekday,
                    'meeting_time' => $group->meeting_time
                        ? substr((string) $group->meeting_time, 0, 5)
                        : null,
                    'contribution' => $plan ? [
                        'name' => $plan->name_i18n[$locale] ?? $plan->name_i18n['fr'] ?? '',
                        'amount_minor' => $plan->amount_minor,
                        'currency' => $plan->currency,
                        'frequency' => $plan->frequency,
                        'due_day' => $plan->due_day,
                    ] : null,
                ];
            });

        return response()->json([
            'groups' => $groups,
        ]);
    }

    public function requestJoin(Request $request, string $groupId)
    {
        $group = DahiraGroup::query()->where('is_active', true)->findOrFail($groupId);

        $data = $request->validate([
            'first_name' => 'required|string|max:60',
            'last_name' => 'required|string|max:60',
            'phone' => 'required|string|max:20',
            'message' => 'nullable|string|max:500',
        ]);

        $phone = preg_replace('/\s+/', '', $data['phone']) ?: $data['phone'];

        $existingPending = DahiraJoinRequest::query()
            ->where('dahira_group_id', $group->id)
            ->where('phone', $phone)
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            return response()->json([
                'message' => 'Une demande est déjà en attente pour ce numéro.',
                'status' => 'pending',
            ], 422);
        }

        $joinRequest = DahiraJoinRequest::query()->create([
            'dahira_group_id' => $group->id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $phone,
            'message' => $data['message'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'id' => $joinRequest->id,
            'status' => $joinRequest->status,
            'message' => 'Demande envoyée. Le secrétariat du dahira la validera.',
        ], 201);
    }
}
