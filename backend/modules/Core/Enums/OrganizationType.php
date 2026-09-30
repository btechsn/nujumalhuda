<?php

declare(strict_types=1);

namespace Modules\Core\Enums;

/**
 * Type d'organisation.
 *
 * - center: Le centre principal (Nujum Al-Huda)
 * - mosque: Une mosquée affiliée ou la mosquée du centre
 * - dahira: Un dahira (groupe spirituel communautaire)
 * - zawiya: Un zawiya (lieu de retraite spirituelle)
 */
enum OrganizationType: string
{
    case CENTER = 'center';
    case MOSQUE = 'mosque';
    case DAHIRA = 'dahira';
    case ZAWIYA = 'zawiya';

    public function label(): string
    {
        return match ($this) {
            self::CENTER => 'Centre',
            self::MOSQUE => 'Mosquée',
            self::DAHIRA => 'Dahira',
            self::ZAWIYA => 'Zawiya',
        };
    }
}
