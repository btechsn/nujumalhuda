<?php

declare(strict_types=1);

namespace Modules\Announcements\Enums;

/**
 * Priorité d'affichage dans le bandeau.
 */
enum AnnouncementPriority: string
{
    case LOW = 'low';
    case NORMAL = 'normal';
    case HIGH = 'high';

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Basse',
            self::NORMAL => 'Normale',
            self::HIGH => 'Haute',
        };
    }

    public function order(): int
    {
        return match ($this) {
            self::HIGH => 1,
            self::NORMAL => 2,
            self::LOW => 3,
        };
    }
}
