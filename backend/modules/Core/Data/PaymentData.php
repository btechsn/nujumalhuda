<?php

declare(strict_types=1);

namespace Modules\Core\Data;

use Modules\Core\Enums\PaymentMethod;
use Modules\Core\Enums\PaymentStatus;
use Modules\Core\Models\Payment;

class PaymentData
{
    public function __construct(
        public ?string $id,
        public string $payable_type,
        public string $payable_id,
        public string $user_id,
        public ?string $organization_id,
        public PaymentMethod $method,
        public PaymentStatus $status,
        public string $currency,
        public int $amount_minor,
        public ?string $gateway_transaction_id = null,
        public ?array $metadata = null,
    ) {
    }

    public static function fromPayment(Payment $payment): self
    {
        return new self(
            id: $payment->id,
            payable_type: $payment->payable_type,
            payable_id: $payment->payable_id,
            user_id: $payment->user_id,
            organization_id: $payment->organization_id,
            method: $payment->method,
            status: $payment->status,
            currency: $payment->currency,
            amount_minor: (int) $payment->amount_minor,
            gateway_transaction_id: $payment->gateway_transaction_id,
            metadata: $payment->metadata,
        );
    }
}
