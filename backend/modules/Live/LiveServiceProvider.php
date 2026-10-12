<?php

namespace Modules\Live;

use Filament\Panel;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Events\MinorAnonymized;
use Modules\Core\Events\OperationsStatusCollecting;
use Modules\Core\Events\PersonalDataPurgeRunning;
use Modules\Live\Listeners\PurgeExpiredLiveChats;
use Modules\Live\Listeners\ReportLiveOperations;
use Modules\Live\Listeners\ScrubAnonymizedRecitations;
use Modules\Core\Contracts\RecitationScheduler;
use Modules\Live\Services\LiveRecitationScheduler;

class LiveServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/mediamtx.php', 'mediamtx');
        $this->app->singleton(RecitationScheduler::class, LiveRecitationScheduler::class);

        Panel::configureUsing(function (Panel $panel): void {
            if ($panel->getId() !== 'admin') {
                return;
            }

            $panel->discoverResources(
                in: __DIR__ . '/Filament/Resources',
                for: 'Modules\\Live\\Filament\\Resources',
            );
        });
    }

    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'live');

        $this->registerObservers();

        Event::listen(PersonalDataPurgeRunning::class, PurgeExpiredLiveChats::class);
        Event::listen(MinorAnonymized::class, ScrubAnonymizedRecitations::class);
        Event::listen(OperationsStatusCollecting::class, ReportLiveOperations::class);

        $this->commands([
            Console\Commands\CheckStreamHealthCommand::class,
            Console\Commands\ArchiveOldStreamsCommand::class,
            Console\Commands\CleanupExpiredChatsCommand::class,
            Console\Commands\SyncYoutubeLivesCommand::class,
        ]);

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('live:sync-youtube')->everyMinute()->withoutOverlapping();
            $schedule->command('live:check-health')->everyMinute();
            $schedule->command('live:archive-old')->daily();
            $schedule->command('live:cleanup-chats')->weekly();
        });
    }

    protected function registerObservers(): void
    {
        Models\LiveStream::observe(Observers\LiveStreamObserver::class);
    }
}
