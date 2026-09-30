<?php

declare(strict_types=1);

namespace Modules\Announcements\Enums;

/**
 * Catégories d'annonces (visuellement différenciées dans le ticker).
 */
enum AnnouncementCategory: string
{
    case CENTER = 'center';
    case COMMUNITY = 'community';
    case URGENT = 'urgent';
    case EVENT = 'event';

    public function label(): string
    {
        return match ($this) {
            self::CENTER => 'Centre',
            self::COMMUNITY => 'Communauté',
            self::URGENT => 'Urgent',
            self::EVENT => 'Événement',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CENTER => 'brand',
            self::COMMUNITY => 'info',
            self::URGENT => 'danger',
            self::EVENT => 'accent',
        };
    }
}
