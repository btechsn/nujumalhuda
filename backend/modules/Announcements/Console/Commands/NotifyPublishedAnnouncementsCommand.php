<?php

declare(strict_types=1);

namespace Modules\Announcements\Console\Commands;

use Illuminate\Console\Command;
use Modules\Announcements\Models\Announcement;
use Modules\Announcements\Services\AnnouncementPushNotifier;

class NotifyPublishedAnnouncementsCommand extends Command
{
    protected $signature = 'announcements:notify-published';

    protected $description = 'Envoie les notifications des annonces au moment de leur publication';

    public function handle(AnnouncementPushNotifier $notifier): int
    {
        $count = 0;
        $now = now();
        $recent = $now->copy()->subMinutes(2);
        Announcement::query()
            ->active()
            ->where(function ($query) use ($now, $recent): void {
                $query->where(function ($query) use ($now, $recent): void {
                    $query->whereNull('starts_at')
                        ->where('created_at', '>=', $recent);
                })->orWhere(function ($query) use ($now, $recent): void {
                    $query->whereNotNull('starts_at')
                        ->whereBetween('starts_at', [$recent, $now]);
                });
            })
            ->with('audiences')
            ->orderBy('starts_at')
            ->chunkById(100, function ($announcements) use ($notifier, &$count): void {
                foreach ($announcements as $announcement) {
                    $count += $notifier->notify($announcement);
                }
            });

        $this->info("Notifications créées pour {$count} destinataire(s).");

        return self::SUCCESS;
    }
}
