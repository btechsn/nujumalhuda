<?php

declare(strict_types=1);

namespace Modules\Announcements\Providers;

use Filament\Panel;
use Illuminate\Support\ServiceProvider;

class AnnouncementsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if ($panel->getId() !== 'admin') {
                return;
            }

            $panel->discoverResources(
                in: dirname(__DIR__).'/Filament/Resources',
                for: 'Modules\\Announcements\\Filament\\Resources',
            );
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->app->register(AnnouncementsEventServiceProvider::class);
    }
}
