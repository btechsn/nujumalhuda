<?php

namespace Modules\Academics;

use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Modules\Academics\Models\StudentProgress;
use Modules\Academics\Observers\StudentProgressObserver;

class AcademicsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if ($panel->getId() !== 'admin') {
                return;
            }

            $panel->discoverResources(
                in: __DIR__ . '/Filament/Resources',
                for: 'Modules\\Academics\\Filament\\Resources',
            );
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'academics');

        StudentProgress::observe(StudentProgressObserver::class);
    }
}
