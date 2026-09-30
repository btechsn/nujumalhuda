<?php

declare(strict_types=1);

namespace Modules\Core\Payments;

use Modules\Core\Contracts\PaymentGateway;
use Modules\Core\Enums\PaymentMethod;

class PaymentGatewayManager
{
    public function __construct(
        private readonly WavePaymentGateway $wave,
        private readonly OrangeMoneyPaymentGateway $orangeMoney,
    ) {
    }

    public function for(PaymentMethod $method): PaymentGateway
    {
        return match ($method) {
            PaymentMethod::WAVE => $this->wave,
            PaymentMethod::ORANGE_MONEY => $this->orangeMoney,
            default => throw new \InvalidArgumentException('Cette méthode n\'utilise pas de passerelle.'),
        };
    }
}
