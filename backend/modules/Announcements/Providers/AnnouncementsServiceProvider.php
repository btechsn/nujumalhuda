<?php

declare(strict_types=1);

namespace Modules\Announcements\Providers;

use Illuminate\Support\ServiceProvider;

class AnnouncementsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->app->register(AnnouncementsEventServiceProvider::class);
    }
}
