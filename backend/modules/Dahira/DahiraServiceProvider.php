<?php

namespace Modules\Dahira;

use Filament\Panel;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Events\PaymentRecorded;
use Modules\Dahira\Listeners\RecordContributionTreasury;

class DahiraServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if ($panel->getId() !== 'admin') {
                return;
            }

            $panel->discoverResources(
                in: __DIR__ . '/Filament/Resources',
                for: 'Modules\\Dahira\\Filament\\Resources',
            );
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        Event::listen(PaymentRecorded::class, RecordContributionTreasury::class);

        $this->commands([
            Console\Commands\GenerateSchedulesCommand::class,
            Console\Commands\RemindDuesCommand::class,
        ]);

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('dahira:generate-schedules')->monthlyOn(1, '6:00');
            $schedule->command('dahira:remind-dues')->weeklyOn(1, '8:00');
        });
    }
}
