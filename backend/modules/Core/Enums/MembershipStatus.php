<?php

declare(strict_types=1);

namespace Modules\Core\Enums;

/**
 * Statut d'adhésion à une organisation.
 */
enum MembershipStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case EXPIRED = 'expired';
    case REVOKED = 'revoked';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::ACTIVE => 'Actif',
            self::SUSPENDED => 'Suspendu',
            self::EXPIRED => 'Expiré',
            self::REVOKED => 'Révoqué',
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }
}
