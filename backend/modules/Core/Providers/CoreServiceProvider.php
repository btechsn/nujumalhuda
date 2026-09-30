<?php

declare(strict_types=1);

namespace Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;
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
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->app->register(CoreEventServiceProvider::class);
    }
}
