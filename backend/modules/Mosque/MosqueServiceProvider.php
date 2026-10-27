<?php

namespace Modules\Mosque;

use Filament\Panel;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Events\PublicSearchRunning;
use Modules\Mosque\Console\Commands\NotifyPrayerTimesCommand;
use Modules\Mosque\Listeners\SearchPublishedKhutbas;
use Modules\Mosque\Services\HijriCalendarService;
use Modules\Mosque\Services\PrayerTimeService;

class MosqueServiceProvider extends ServiceProvider
{
    protected string $namespace = 'Modules\Mosque';

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        Event::listen(PublicSearchRunning::class, SearchPublishedKhutbas::class);
        
        // Register observers
        \Modules\Mosque\Models\PrayerTime::observe(\Modules\Mosque\Observers\PrayerTimeObserver::class);
        
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Mosque\Console\Commands\NotifyUpcomingPrayerCommand::class,
                NotifyPrayerTimesCommand::class,
            ]);
        }

        $this->app->booted(function (): void {
            $this->app->make(Schedule::class)
                ->command('mosque:notify-prayer-time')
                ->everyMinute()
                ->withoutOverlapping();
        });
    }

    public function register(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if ($panel->getId() !== 'admin') {
                return;
            }

            $panel->discoverResources(
                in: __DIR__ . '/Filament/Resources',
                for: 'Modules\\Mosque\\Filament\\Resources',
            );
        });

        // Enregistrer les services
        $this->app->singleton(PrayerTimeService::class);
        $this->app->singleton(HijriCalendarService::class);
    }
}
