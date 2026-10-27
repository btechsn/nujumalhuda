<?php

declare(strict_types=1);

namespace Modules\Mosque\Console\Commands;

use Illuminate\Console\Command;
use Modules\Mosque\Services\PrayerTimePushNotifier;

class NotifyPrayerTimesCommand extends Command
{
    protected $signature = 'mosque:notify-prayer-time';

    protected $description = 'Envoie une notification push à chaque heure de prière';

    public function handle(PrayerTimePushNotifier $notifier): int
    {
        $count = $notifier->notifyCurrentPrayers();
        $this->info("Notifications de prière créées pour {$count} destinataire(s).");

        return self::SUCCESS;
    }
}
