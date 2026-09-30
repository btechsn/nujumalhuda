<?php

namespace Modules\Community\Listeners;

use Modules\Community\Models\Donation;
use Modules\Core\Events\PaymentRecorded;

class MarkDonationPaid
{
    public function handle(PaymentRecorded $event): void
    {
        if ($event->payment->payable_type !== Donation::class) {
            return;
        }

        Donation::query()->whereKey($event->payment->payable_id)->update([
            'status' => 'completed',
            'paid_at' => now(),
        ]);
    }
}
