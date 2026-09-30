<?php

declare(strict_types=1);

namespace Modules\Core\Enums;

/**
 * Statut de modération d'un contenu.
 */
enum ModerationStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case FLAGGED = 'flagged';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::APPROVED => 'Approuvé',
            self::REJECTED => 'Rejeté',
            self::FLAGGED => 'Signalé',
        };
    }

    public function isApproved(): bool
    {
        return $this === self::APPROVED;
    }
}
