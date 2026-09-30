<?php

declare(strict_types=1);

namespace Modules\Core\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Core\Events\MembershipCreated;
use Modules\Core\Events\PaymentRecorded;
use Modules\Core\Events\UserRegistered;
use Modules\Core\Listeners\SendWelcomeNotification;

class CoreEventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        UserRegistered::class => [
            SendWelcomeNotification::class,
        ],
        
        MembershipCreated::class => [
            // Listeners pour la création d'adhésion
        ],
        
        PaymentRecorded::class => [
            // Listeners pour les paiements enregistrés
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
