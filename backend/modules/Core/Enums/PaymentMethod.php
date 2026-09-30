<?php

declare(strict_types=1);

namespace Modules\Core\Enums;

/**
 * Méthodes de paiement disponibles.
 */
enum PaymentMethod: string
{
    case WAVE = 'wave';
    case ORANGE_MONEY = 'orange_money';
    case CASH = 'cash';
    case BANK_TRANSFER = 'bank_transfer';

    public function label(): string
    {
        return match ($this) {
            self::WAVE => 'Wave',
            self::ORANGE_MONEY => 'Orange Money',
            self::CASH => 'Espèces',
            self::BANK_TRANSFER => 'Virement bancaire',
        };
    }

    public function requiresGateway(): bool
    {
        return in_array($this, [
            self::WAVE,
            self::ORANGE_MONEY,
        ]);
    }
}
