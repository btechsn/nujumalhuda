<?php

declare(strict_types=1);

namespace Modules\Core\Listeners;

use Modules\Core\Enums\NotificationChannel;
use Modules\Core\Events\UserRegistered;
use Modules\Core\Models\Notification;

class SendWelcomeNotification
{
    public function handle(UserRegistered $event): void
    {
        Notification::create([
            'user_id' => $event->user->id,
            'type' => 'auth.welcome',
            'channel' => NotificationChannel::IN_APP,
            'title' => 'Bienvenue',
            'message' => 'Votre compte Nujum Al-Huda est créé.',
            'sent_at' => now(),
        ]);
    }
}
