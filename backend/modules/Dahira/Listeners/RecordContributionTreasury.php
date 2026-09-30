<?php

namespace Modules\Dahira\Listeners;

use Modules\Core\Events\PaymentRecorded;
use Modules\Dahira\Models\Contribution;
use Modules\Dahira\Models\TreasuryEntry;

class RecordContributionTreasury
{
    public function handle(PaymentRecorded $event): void
    {
        if ($event->payment->payable_type !== Contribution::class) {
            return;
        }

        $contribution = Contribution::query()->with('schedule')->find($event->payment->payable_id);
        if (!$contribution) {
            return;
        }

        $contribution->schedule?->update(['status' => 'paid']);

        $exists = TreasuryEntry::query()->where('payment_id', $event->payment->id)->exists();
        if ($exists) {
            return;
        }

        TreasuryEntry::create([
            'dahira_group_id' => $contribution->dahira_group_id,
            'direction' => 'in',
            'category' => 'contribution',
            'amount_minor' => $contribution->amount_minor,
            'currency' => 'XOF',
            'label' => 'Cotisation',
            'occurred_on' => now()->toDateString(),
            'contribution_id' => $contribution->id,
            'payment_id' => $event->payment->id,
            'recorded_by' => $contribution->recorded_by,
        ]);
    }
}
