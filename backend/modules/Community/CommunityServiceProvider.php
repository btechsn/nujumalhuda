<?php

namespace Modules\Community;

use Filament\Panel;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Community\Listeners\MarkDonationPaid;
use Modules\Community\Models\Question;
use Modules\Community\Models\Testimonial;
use Modules\Core\Events\PaymentRecorded;
use Modules\Core\Services\ModerationService;

class CommunityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if ($panel->getId() !== 'admin') {
                return;
            }

            $panel->discoverResources(
                in: __DIR__ . '/Filament/Resources',
                for: 'Modules\\Community\\Filament\\Resources',
            );
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        Event::listen(PaymentRecorded::class, MarkDonationPaid::class);

        Question::saved(function (Question $question): void {
            app(ModerationService::class)->sync($question, (string) $question->status);
        });
        Testimonial::saved(function (Testimonial $testimonial): void {
            app(ModerationService::class)->sync($testimonial, (string) $testimonial->status);
        });

        $this->commands([
            Console\Commands\PrepareSmsDigestCommand::class,
        ]);

        $this->app->booted(function () {
            $this->app->make(Schedule::class)->command('community:prepare-digest')->monthlyOn(1, '8:00');
        });
    }
}
