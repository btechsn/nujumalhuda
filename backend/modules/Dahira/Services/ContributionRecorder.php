<?php

namespace Modules\Dahira\Services;

use Modules\Core\Enums\PaymentMethod;
use Modules\Core\Enums\PaymentStatus;
use Modules\Core\Models\Payment;
use Modules\Core\Models\User;
use Modules\Dahira\Models\Contribution;
use Modules\Dahira\Models\ContributionSchedule;
use Modules\Dahira\Models\TreasuryEntry;

class ContributionRecorder
{
    public function record(
        ContributionSchedule $schedule,
        User $recorder,
        string $method = 'cash',
        ?string $note = null,
        ?string $paidOn = null,
    ): Contribution {
        $schedule->load('plan.group', 'membership');
        $paymentMethod = PaymentMethod::from($method);

        $contribution = Contribution::create([
            'dahira_group_id' => $schedule->plan->dahira_group_id,
            'membership_id' => $schedule->membership_id,
            'schedule_id' => $schedule->id,
            'recorded_by' => $recorder->id,
            'amount_minor' => $schedule->amount_minor,
            'currency' => 'XOF',
            'paid_on' => $paidOn ?? now()->toDateString(),
            'note' => $note,
        ]);

        $payment = Payment::create([
            'payable_type' => Contribution::class,
            'payable_id' => $contribution->id,
            'user_id' => $schedule->membership->user_id,
            'organization_id' => $schedule->plan->group->organization_id,
            'method' => $paymentMethod,
            'status' => PaymentStatus::COMPLETED,
            'currency' => 'XOF',
            'amount_minor' => $schedule->amount_minor,
            'completed_at' => now(),
            'metadata' => ['source' => 'dahira_contribution'],
        ]);

        $contribution->update(['payment_id' => $payment->id]);

        $schedule->update(['status' => 'paid']);

        TreasuryEntry::create([
            'dahira_group_id' => $contribution->dahira_group_id,
            'direction' => 'in',
            'category' => 'contribution',
            'amount_minor' => $contribution->amount_minor,
            'currency' => 'XOF',
            'label' => 'Cotisation',
            'occurred_on' => $contribution->paid_on,
            'contribution_id' => $contribution->id,
            'payment_id' => $payment->id,
            'recorded_by' => $recorder->id,
            'note' => $note,
        ]);

        return $contribution->fresh('payment');
    }
}
