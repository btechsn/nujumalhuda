<?php

namespace Modules\Dahira\Services;

use Modules\Core\Models\Membership;
use Modules\Dahira\Models\ContributionPlan;
use Modules\Dahira\Models\ContributionSchedule;

class ScheduleGenerator
{
    public function generateForMonth(?\DateTimeInterface $month = null): int
    {
        $month = $month ? \Carbon\Carbon::parse($month) : now();
        $periodStart = $month->copy()->startOfMonth()->toDateString();
        $created = 0;

        $plans = ContributionPlan::query()->where('is_active', true)->with('group')->get();

        foreach ($plans as $plan) {
            if ($plan->frequency !== 'monthly') {
                continue;
            }

            $members = Membership::query()
                ->where('organization_id', $plan->group->organization_id)
                ->where('status', 'active')
                ->get();

            $dueDay = min(max((int) $plan->due_day, 1), 28);
            $dueOn = $month->copy()->startOfMonth()->day($dueDay)->toDateString();

            foreach ($members as $member) {
                $schedule = ContributionSchedule::firstOrCreate(
                    [
                        'plan_id' => $plan->id,
                        'membership_id' => $member->id,
                        'period_start' => $periodStart,
                    ],
                    [
                        'due_on' => $dueOn,
                        'amount_minor' => $plan->amount_minor,
                        'status' => 'due',
                    ]
                );

                if ($schedule->wasRecentlyCreated) {
                    $created++;
                }
            }
        }

        ContributionSchedule::query()
            ->where('status', 'due')
            ->whereDate('due_on', '<', now()->toDateString())
            ->update(['status' => 'overdue']);

        return $created;
    }
}
