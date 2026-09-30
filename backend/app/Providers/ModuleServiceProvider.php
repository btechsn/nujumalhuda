<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Enregistre les modules de l'application.
     */
    public function register(): void
    {
        $modules = config('modules.modules', []);
        $modulesPath = config('modules.path');
        $namespace = config('modules.namespace');

        foreach ($modules as $module) {
            $providerClass = "{$namespace}\\{$module}\\Providers\\{$module}ServiceProvider";

            if (class_exists($providerClass)) {
                $this->app->register($providerClass);
            }
        }
    }

    /**
     * Bootstrap des services de modules.
     */
    public function boot(): void
    {
        if (config('modules.migrations')) {
            $this->loadModuleMigrations();
        }

        if (config('modules.auto_discovery')) {
            $this->loadModuleRoutes();
            $this->loadModuleViews();
            $this->loadModuleTranslations();
        }
    }

    /**
     * Charge les migrations de tous les modules actifs.
     */
    protected function loadModuleMigrations(): void
    {
        $modules = config('modules.modules', []);
        $modulesPath = config('modules.path');

        foreach ($modules as $module) {
            $migrationsPath = "{$modulesPath}/{$module}/Database/Migrations";

            if (File::isDirectory($migrationsPath)) {
                $this->loadMigrationsFrom($migrationsPath);
            }
        }
    }

    /**
     * Charge les routes API de tous les modules actifs.
     */
    protected function loadModuleRoutes(): void
    {
        $modules = config('modules.modules', []);
        $modulesPath = config('modules.path');

        foreach ($modules as $module) {
            $apiRoutesPath = "{$modulesPath}/{$module}/routes/api.php";

            if (File::exists($apiRoutesPath)) {
                Route::prefix('api/v1')
                    ->middleware('api')
                    ->name('api.')
                    ->group($apiRoutesPath);
            }

            $channelsPath = "{$modulesPath}/{$module}/routes/channels.php";

            if (File::exists($channelsPath)) {
                require $channelsPath;
            }
        }
    }

    /**
     * Charge les vues de tous les modules actifs.
     */
    protected function loadModuleViews(): void
    {
        $modules = config('modules.modules', []);
        $modulesPath = config('modules.path');

        foreach ($modules as $module) {
            $viewsPath = "{$modulesPath}/{$module}/resources/views";

            if (File::isDirectory($viewsPath)) {
                $this->loadViewsFrom($viewsPath, strtolower($module));
            }
        }
    }

    /**
     * Charge les traductions de tous les modules actifs.
     */
    protected function loadModuleTranslations(): void
    {
        $modules = config('modules.modules', []);
        $modulesPath = config('modules.path');

        foreach ($modules as $module) {
            $langPath = "{$modulesPath}/{$module}/resources/lang";

            if (File::isDirectory($langPath)) {
                $this->loadTranslationsFrom($langPath, strtolower($module));
            }
        }
    }
}
