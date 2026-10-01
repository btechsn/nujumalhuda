<?php

namespace App\Providers;

use App\Database\PostgresConnection;
use Filament\Actions\Action;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Support\Fluent;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Support\SiteSettings;
use Modules\Academics\AcademicsServiceProvider;
use Modules\Announcements\Providers\AnnouncementsServiceProvider;
use Modules\Community\CommunityServiceProvider;
use Modules\Dahira\DahiraServiceProvider;
use Modules\Core\Providers\CoreServiceProvider;
use Modules\Education\EducationServiceProvider;
use Modules\Live\LiveServiceProvider;
use Modules\Mosque\MosqueServiceProvider;
use Modules\News\NewsServiceProvider;
use Modules\Resources\ResourcesServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Les modules à charger
     */
    protected array $modules = [
        CoreServiceProvider::class,
        AnnouncementsServiceProvider::class,
        EducationServiceProvider::class,
        MosqueServiceProvider::class,
        NewsServiceProvider::class,
        LiveServiceProvider::class,
        ResourcesServiceProvider::class,
        AcademicsServiceProvider::class,
        CommunityServiceProvider::class,
        DahiraServiceProvider::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        Connection::resolverFor('pgsql', function ($connection, $database, $prefix, $config) {
            return new PostgresConnection($connection, $database, $prefix, $config);
        });

        // Enregistrer tous les modules
        foreach ($this->modules as $moduleProvider) {
            $this->app->register($moduleProvider);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Laravel 11 n'expose pas Blueprint::check ; les migrations l'utilisent pour Postgres.
        Blueprint::macro('check', function (string $expression) {
            return $this->addCommand('check', ['expression' => $expression]);
        });

        Grammar::macro('compileCheck', function (Blueprint $blueprint, Fluent $command) {
            $expression = (string) $command->expression;
            $name = $blueprint->getTable().'_chk_'.substr(md5($expression), 0, 10);

            return sprintf(
                'alter table %s add constraint %s check (%s)',
                $this->wrapTable($blueprint),
                $this->wrap($name),
                $expression
            );
        });

        SiteSettings::apply();

        Action::configureUsing(function (Action $action): void {
            if ($action::class === Action::class) {
                return;
            }

            $action->iconButton();

            if (blank($action->getIcon()) && filled($icon = $action->getGroupedIcon())) {
                $action->icon($icon);
            }
        }, isImportant: true);

        TableAction::configureUsing(function (TableAction $action): void {
            if ($action instanceof BulkAction) {
                return;
            }

            $action->iconButton();

            if (blank($action->getIcon()) && filled($icon = $action->getGroupedIcon())) {
                $action->icon($icon);
            }
        }, isImportant: true);
    }
}
