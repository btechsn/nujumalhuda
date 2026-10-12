<?php

declare(strict_types=1);

namespace Modules\Announcements\Actions;

use Modules\Announcements\Events\AnnouncementBroadcast;
use Modules\Announcements\Models\Announcement;

class BroadcastAnnouncementAction
{
    public function execute(Announcement $announcement): void
    {
        if (!$announcement->isVisible()) {
            return;
        }

        try {
            broadcast(new AnnouncementBroadcast($announcement));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
