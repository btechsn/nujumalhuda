<?php

declare(strict_types=1);

namespace Modules\Announcements\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Announcements\Models\Announcement;

class AnnouncementCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Announcement $announcement)
    {
    }
}
