<?php

declare(strict_types=1);

namespace Modules\Core\Providers;

use Filament\Panel;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Console\Commands\CreateEncryptedBackupCommand;
use Modules\Core\Console\Commands\OperationsStatusCommand;
use Modules\Core\Console\Commands\PurgeExpiredPersonalDataCommand;
use Modules\Core\Console\Commands\VerifyEncryptedBackupCommand;
use Modules\Core\Contracts\MediaStorage;
use Modules\Core\Contracts\PaymentGateway;
use Modules\Core\Payments\OrangeMoneyPaymentGateway;
use Modules\Core\Payments\WavePaymentGateway;
use Modules\Core\Storage\LocalMediaStorage;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MediaStorage::class, LocalMediaStorage::class);
        $this->app->singleton(WavePaymentGateway::class);
        $this->app->singleton(OrangeMoneyPaymentGateway::class);
        $this->app->bind(PaymentGateway::class, WavePaymentGateway::class);

        Panel::configureUsing(function (Panel $panel): void {
            if ($panel->getId() !== 'admin') {
                return;
            }

            $panel->discoverResources(
                in: __DIR__.'/../Filament/Resources',
                for: 'Modules\\Core\\Filament\\Resources',
            );
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->app->register(CoreEventServiceProvider::class);

        $this->commands([
            PurgeExpiredPersonalDataCommand::class,
            CreateEncryptedBackupCommand::class,
            VerifyEncryptedBackupCommand::class,
            OperationsStatusCommand::class,
        ]);

        $this->app->booted(function (): void {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('backup:encrypted')->dailyAt('02:30')->withoutOverlapping();
            $schedule->command('privacy:purge')->dailyAt('03:15')->withoutOverlapping();
            $schedule->command('backup:restore-test')->weeklyOn(0, '04:00')->withoutOverlapping();
            $schedule->command('operations:status')->dailyAt('04:30')->withoutOverlapping();
        });
    }
}
