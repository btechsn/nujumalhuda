<?php

declare(strict_types=1);

namespace Modules\Announcements\Enums;

/**
 * Type de ciblage d'audience pour une annonce.
 */
enum AudienceType: string
{
    case PUBLIC = 'public';
    case MEMBERS = 'members';
    case ROLE = 'role';
    case ORGANIZATION = 'organization';
    case COHORT = 'cohort';

    public function label(): string
    {
        return match ($this) {
            self::PUBLIC => 'Tout public',
            self::MEMBERS => 'Membres authentifiés',
            self::ROLE => 'Porteurs d\'un rôle',
            self::ORGANIZATION => 'Membres d\'une organisation',
            self::COHORT => 'Élèves d\'une promotion',
        };
    }
}
