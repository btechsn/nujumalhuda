<?php

declare(strict_types=1);

namespace Modules\Core\Data;

use Modules\Core\Enums\NotificationChannel;

class NotificationData
{
    public function __construct(
        public string $user_id,
        public string $type,
        public NotificationChannel $channel,
        public string $title,
        public string $message,
        public ?array $data = null,
        public ?string $action_url = null,
    ) {
    }
}
