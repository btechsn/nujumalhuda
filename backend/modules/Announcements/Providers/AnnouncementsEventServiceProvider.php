<?php

declare(strict_types=1);

namespace Modules\Announcements\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Announcements\Events\AnnouncementBroadcast;
use Modules\Announcements\Events\AnnouncementCreated;

class AnnouncementsEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        AnnouncementCreated::class => [
            // Listeners pour nouvelle annonce
        ],
    ];

    public function boot(): void
    {
        //
    }
}
