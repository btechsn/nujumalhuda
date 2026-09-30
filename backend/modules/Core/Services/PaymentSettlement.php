<?php

declare(strict_types=1);

namespace Modules\Core\Services;

use Modules\Core\Events\PaymentRecorded;
use Modules\Core\Models\Payment;

class PaymentSettlement
{
    public function complete(Payment $payment, array $gatewayPayload = []): bool
    {
        if ($payment->status->value === 'completed') {
            return true;
        }

        if (($gatewayPayload['status'] ?? 'completed') !== 'completed') {
            return false;
        }

        $payment->markAsCompleted($gatewayPayload['transaction_id'] ?? $payment->gateway_transaction_id);
        if ($gatewayPayload !== []) {
            $payment->update(['gateway_response' => $gatewayPayload['raw'] ?? $gatewayPayload]);
        }

        event(new PaymentRecorded($payment->fresh()));

        return true;
    }
}
