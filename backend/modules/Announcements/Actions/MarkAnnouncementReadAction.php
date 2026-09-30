<?php

declare(strict_types=1);

namespace Modules\Announcements\Actions;

use Modules\Announcements\Models\Announcement;
use Modules\Announcements\Models\AnnouncementRead;
use Modules\Core\Models\User;

class MarkAnnouncementReadAction
{
    public function execute(Announcement $announcement, User $user): void
    {
        AnnouncementRead::firstOrCreate([
            'announcement_id' => $announcement->id,
            'user_id' => $user->id,
        ], [
            'read_at' => now(),
        ]);
    }
}
