<?php

declare(strict_types=1);

namespace Modules\Core\Enums;

/**
 * Canaux de notification disponibles.
 */
enum NotificationChannel: string
{
    case IN_APP = 'in_app';
    case EMAIL = 'email';
    case SMS = 'sms';
    case PUSH = 'push';

    public function label(): string
    {
        return match ($this) {
            self::IN_APP => 'Dans l\'application',
            self::EMAIL => 'Email',
            self::SMS => 'SMS',
            self::PUSH => 'Notification push',
        };
    }
}
