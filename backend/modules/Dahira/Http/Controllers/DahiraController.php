<?php

namespace Modules\Dahira\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Models\Membership;
use Modules\Core\Models\Role;
use Modules\Dahira\Models\ContributionSchedule;
use Modules\Dahira\Models\DahiraGroup;
use Modules\Dahira\Models\Meeting;
use Modules\Dahira\Models\MeetingAttendance;
use Modules\Dahira\Models\TreasuryEntry;
use Modules\Dahira\Services\ContributionRecorder;
use Modules\Dahira\Services\DahiraAccess;
use Modules\Dahira\Services\DahiraNotifier;

class DahiraController extends Controller
{
    public function index(Request $request, DahiraAccess $access)
    {
        return DahiraGroup::query()
            ->where('is_active', true)
            ->get()
            ->filter(fn (DahiraGroup $group) => $access->isMember($request->user(), $group))
            ->values();
    }

    public function show(Request $request, string $id, DahiraAccess $access)
    {
        $group = DahiraGroup::with('organization')->findOrFail($id);
        if (!$access->isMember($request->user(), $group)) {
            abort(403);
        }

        return response()->json([
            'group' => $group,
            'balance_minor' => $access->isTreasurer($request->user(), $group) ? $group->balanceMinor() : null,
        ]);
    }

    public function members(Request $request, string $id, DahiraAccess $access)
    {
        $group = DahiraGroup::findOrFail($id);
        if (!$access->isOfficer($request->user(), $group)) {
            abort(403);
        }

        return Membership::query()
            ->with('user:id,first_name,last_name,phone', 'role:id,name,display_name')
            ->where('organization_id', $group->organization_id)
            ->get();
    }

    public function join(Request $request, string $id)
    {
        $group = DahiraGroup::findOrFail($id);
        $role = Role::where('name', 'dahira_member')->firstOrFail();

        $membership = Membership::firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'organization_id' => $group->organization_id,
                'role_id' => $role->id,
            ],
            [
                'status' => 'pending',
                'joined_at' => now(),
            ]
        );

        return response()->json($membership, 201);
    }

    public function schedules(Request $request, string $id, DahiraAccess $access)
    {
        $group = DahiraGroup::findOrFail($id);
        $membership = $access->membership($request->user(), $group);
        if (!$membership) {
            abort(403);
        }

        $query = ContributionSchedule::query()
            ->whereHas('plan', fn ($q) => $q->where('dahira_group_id', $group->id));

        if (!$access->isTreasurer($request->user(), $group)) {
            $query->where('membership_id', $membership->id);
        }

        return $query->orderBy('due_on')->get();
    }

    public function pay(Request $request, string $scheduleId, ContributionRecorder $recorder, DahiraAccess $access)
    {
        $schedule = ContributionSchedule::with('plan.group', 'membership')->findOrFail($scheduleId);
        $group = $schedule->plan->group;
        $mine = $schedule->membership->user_id === $request->user()->id;

        if (!$mine && !$access->isTreasurer($request->user(), $group)) {
            abort(403);
        }

        if (in_array($schedule->status, ['paid', 'processing'], true)) {
            return response()->json(['message' => 'Cette échéance est déjà réglée ou en cours de paiement'], 422);
        }

        $data = $request->validate([
            'method' => 'nullable|in:cash,wave,orange_money,bank_transfer',
            'note' => 'nullable|string|max:500',
        ]);

        $contribution = $recorder->record(
            $schedule,
            $request->user(),
            $data['method'] ?? 'cash',
            $data['note'] ?? null
        );

        return response()->json([
            'contribution_id' => $contribution->id,
            'payment_status' => $contribution->payment->status->value,
            'schedule_status' => $schedule->fresh()->status,
            'checkout_url' => $contribution->checkout_url,
            'gateway_error' => $contribution->gateway_error,
        ], $contribution->gateway_error ? 502 : 201);
    }

    public function treasury(Request $request, string $id, DahiraAccess $access)
    {
        $group = DahiraGroup::findOrFail($id);
        if (!$access->isTreasurer($request->user(), $group)) {
            abort(403);
        }

        return response()->json([
            'balance_minor' => $group->balanceMinor(),
            'entries' => TreasuryEntry::where('dahira_group_id', $group->id)->latest('occurred_on')->limit(100)->get(),
        ]);
    }

    public function expense(Request $request, string $id, DahiraAccess $access)
    {
        $group = DahiraGroup::findOrFail($id);
        if (!$access->isTreasurer($request->user(), $group)) {
            abort(403);
        }

        $data = $request->validate([
            'amount_minor' => 'required|integer|min:1',
            'label' => 'required|string|max:160',
            'note' => 'nullable|string|max:500',
            'occurred_on' => 'nullable|date',
        ]);

        $entry = TreasuryEntry::create([
            'dahira_group_id' => $group->id,
            'direction' => 'out',
            'category' => 'expense',
            'amount_minor' => $data['amount_minor'],
            'label' => $data['label'],
            'occurred_on' => $data['occurred_on'] ?? now()->toDateString(),
            'recorded_by' => $request->user()->id,
            'note' => $data['note'] ?? null,
        ]);

        return response()->json($entry, 201);
    }

    public function meetings(Request $request, string $id, DahiraAccess $access)
    {
        $group = DahiraGroup::findOrFail($id);
        if (!$access->isMember($request->user(), $group)) {
            abort(403);
        }

        return Meeting::where('dahira_group_id', $group->id)->orderByDesc('starts_at')->get();
    }

    public function storeMeeting(Request $request, string $id, DahiraAccess $access, DahiraNotifier $notifier)
    {
        $group = DahiraGroup::findOrFail($id);
        if (!$access->isOfficer($request->user(), $group)) {
            abort(403);
        }

        $data = $request->validate([
            'title' => 'required|string|max:160',
            'starts_at' => 'required|date',
            'location' => 'nullable|string|max:160',
            'agenda' => 'nullable|string',
            'convene' => 'boolean',
        ]);

        $meeting = Meeting::create([
            'dahira_group_id' => $group->id,
            'title' => $data['title'],
            'starts_at' => $data['starts_at'],
            'location' => $data['location'] ?? $group->location,
            'agenda' => $data['agenda'] ?? null,
        ]);

        if ($request->boolean('convene')) {
            $notifier->convene($meeting);
        }

        return response()->json($meeting, 201);
    }

    public function attendance(Request $request, string $meetingId, DahiraAccess $access)
    {
        $meeting = Meeting::with('group')->findOrFail($meetingId);
        if (!$access->isOfficer($request->user(), $meeting->group)) {
            abort(403);
        }

        $data = $request->validate([
            'entries' => 'required|array',
            'entries.*.membership_id' => 'required|exists:memberships,id',
            'entries.*.status' => 'required|in:present,absent,excused',
        ]);

        foreach ($data['entries'] as $entry) {
            MeetingAttendance::updateOrCreate(
                ['meeting_id' => $meeting->id, 'membership_id' => $entry['membership_id']],
                ['status' => $entry['status']]
            );
        }

        return response()->json(['saved' => count($data['entries'])]);
    }

    public function announce(Request $request, string $id, DahiraAccess $access, DahiraNotifier $notifier)
    {
        $group = DahiraGroup::findOrFail($id);
        if (!$access->isOfficer($request->user(), $group)) {
            abort(403);
        }

        $data = $request->validate([
            'title' => 'required|string|max:160',
            'message' => 'required|string|max:1000',
        ]);

        $announcement = $notifier->announce($group, $data['title'], $data['message']);

        return response()->json(['id' => $announcement->id], 201);
    }
}
