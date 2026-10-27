<?php

declare(strict_types=1);

namespace Modules\Announcements\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Announcements\Events\AnnouncementBroadcast;
use Modules\Announcements\Events\AnnouncementCreated;
use Modules\Announcements\Listeners\NotifyAnnouncementAudience;

class AnnouncementsEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        AnnouncementCreated::class => [
            NotifyAnnouncementAudience::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
