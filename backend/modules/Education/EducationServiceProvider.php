<?php

namespace Modules\Education;

use Filament\Panel;
use Illuminate\Support\ServiceProvider;

class EducationServiceProvider extends ServiceProvider
{
    /**
     * Namespace du module
     */
    protected string $namespace = 'Modules\Education';

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        // Charger les migrations
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        // Charger les routes
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        
        // Charger les vues
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'education');

        // Publier les configs (si nécessaire dans le futur)
        // $this->publishes([
        //     __DIR__ . '/config/education.php' => config_path('education.php'),
        // ], 'education-config');
    }

    /**
     * Register services
     */
    public function register(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if ($panel->getId() !== 'admin') {
                return;
            }

            $panel->discoverResources(
                in: __DIR__ . '/Filament/Resources',
                for: 'Modules\\Education\\Filament\\Resources',
            );
        });

        // Enregistrer l'EventServiceProvider
        $this->app->register(\Modules\Education\Providers\EventServiceProvider::class);
        
        // Enregistrer les services
        $this->app->singleton(\Modules\Education\Services\PdfGeneratorService::class);
        
        // Enregistrer les configs
        // $this->mergeConfigFrom(__DIR__ . '/config/education.php', 'education');
    }
}
