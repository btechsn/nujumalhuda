<?php

namespace Modules\Resources;

use Filament\Panel;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Modules\Resources\Console\Commands\NotifyDailyHadithCommand;

class ResourcesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(base_path('config/zakat.php'), 'zakat');

        Panel::configureUsing(function (Panel $panel): void {
            if ($panel->getId() !== 'admin') {
                return;
            }

            $panel->discoverResources(
                in: __DIR__ . '/Filament/Resources',
                for: 'Modules\\Resources\\Filament\\Resources',
            );
        });
    }

    public function boot(): void
    {
        $this->commands([NotifyDailyHadithCommand::class]);

        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        $this->app->booted(function (): void {
            $this->app->make(Schedule::class)
                ->command('resources:notify-daily-hadith')
                ->dailyAt('08:00')
                ->timezone('Africa/Dakar')
                ->withoutOverlapping();
        });
    }
}
