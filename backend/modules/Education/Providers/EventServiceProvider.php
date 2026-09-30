<?php

namespace Modules\Education\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Education\Events\EnrollmentApproved;
use Modules\Education\Events\EnrollmentRejected;
use Modules\Education\Listeners\SendEnrollmentApprovedEmail;
use Modules\Education\Listeners\SendEnrollmentRejectedEmail;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        EnrollmentApproved::class => [
            SendEnrollmentApprovedEmail::class,
        ],
        EnrollmentRejected::class => [
            SendEnrollmentRejectedEmail::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
