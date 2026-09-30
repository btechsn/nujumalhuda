<?php

namespace Modules\News;

use Filament\Panel;
use Illuminate\Support\ServiceProvider;

class NewsServiceProvider extends ServiceProvider
{
    protected string $namespace = 'Modules\News';

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
    }

    public function register(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if ($panel->getId() !== 'admin') {
                return;
            }

            $panel->discoverResources(
                in: __DIR__ . '/Filament/Resources',
                for: 'Modules\\News\\Filament\\Resources',
            );
        });

        $this->app->register(\Modules\News\Providers\EventServiceProvider::class);
        $this->app->singleton(\Modules\News\Services\CommentSpamService::class);
    }
}
