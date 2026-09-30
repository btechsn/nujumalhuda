<?php

namespace Modules\Mosque\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Mosque\Events\UpcomingPrayerNotification;
use Modules\Mosque\Models\PrayerTime;

class NotifyUpcomingPrayerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mosque:notify-upcoming-prayer
                            {--minutes=15 : Nombre de minutes avant la prière}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie une notification pour la prochaine prière';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $minutesBefore = (int) $this->option('minutes');
        $now = Carbon::now();

        // Trouver les prières qui commencent dans X minutes
        $upcomingPrayers = PrayerTime::whereDate('date', $now->toDateString())
            ->get()
            ->filter(function ($prayer) use ($now, $minutesBefore) {
                $prayerTime = Carbon::parse($prayer->date . ' ' . $prayer->display_time);
                $diff = $now->diffInMinutes($prayerTime, false);
                
                // Prière dans exactement X minutes (+/- 1 minute de tolérance)
                return $diff >= ($minutesBefore - 1) && $diff <= ($minutesBefore + 1);
            });

        if ($upcomingPrayers->isEmpty()) {
            $this->info('Aucune prière à venir dans les ' . $minutesBefore . ' minutes.');
            return self::SUCCESS;
        }

        foreach ($upcomingPrayers as $prayer) {
            $prayerTime = Carbon::parse($prayer->date . ' ' . $prayer->display_time);
            $minutesRemaining = (int) $now->diffInMinutes($prayerTime, false);

            // Broadcast l'événement
            event(new UpcomingPrayerNotification($prayer, $minutesRemaining));

            $this->info("✅ Notification envoyée pour {$prayer->prayer_name} (dans {$minutesRemaining} min)");
        }

        return self::SUCCESS;
    }
}
