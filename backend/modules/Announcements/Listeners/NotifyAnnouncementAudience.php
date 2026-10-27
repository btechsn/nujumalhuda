<?php

declare(strict_types=1);

namespace Modules\Announcements\Listeners;

use Modules\Announcements\Events\AnnouncementCreated;
use Modules\Announcements\Services\AnnouncementPushNotifier;

class NotifyAnnouncementAudience
{
    public function handle(AnnouncementCreated $event): void
    {
        app(AnnouncementPushNotifier::class)->notify($event->announcement);
    }
}
