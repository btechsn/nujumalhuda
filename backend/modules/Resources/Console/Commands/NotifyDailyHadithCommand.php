<?php

declare(strict_types=1);

namespace Modules\Resources\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Enums\NotificationChannel;
use Modules\Core\Jobs\DeliverPushNotification;
use Modules\Core\Models\Notification;
use Modules\Core\Models\NotificationPreference;
use Modules\Core\Models\PushSubscription;
use Modules\Core\Models\User;
use Modules\Core\Support\EditorialText;
use Modules\Resources\Models\DailyContent;

class NotifyDailyHadithCommand extends Command
{
    protected $signature = 'resources:notify-daily-hadith';

    protected $description = 'Envoie le hadith du jour aux utilisateurs abonnés';

    public function handle(): int
    {
        $today = now('Africa/Dakar')->toDateString();
        $hadith = DailyContent::query()
            ->where('type', 'hadith')
            ->where('is_published', true)
            ->whereDate('display_date', $today)
            ->first();

        if (!$hadith) {
            $this->info("Aucun hadith publié pour le {$today}.");

            return self::SUCCESS;
        }

        $userIds = NotificationPreference::query()
            ->where('category', 'hadith')
            ->where('channel', NotificationChannel::PUSH)
            ->where('enabled', true)
            ->pluck('user_id');

        $userIds = PushSubscription::query()
            ->whereIn('user_id', $userIds)
            ->distinct()
            ->pluck('user_id');

        $created = 0;
        User::query()->whereIn('id', $userIds)->select(['id', 'locale'])->chunkById(100, function ($users) use ($hadith, $today, &$created): void {
            foreach ($users as $user) {
                $exists = Notification::query()
                    ->where('user_id', $user->id)
                    ->where('type', 'hadith.daily')
                    ->where('data->content_id', $hadith->id)
                    ->exists();
                if ($exists) {
                    continue;
                }

                $locale = in_array($user->locale, ['fr', 'en', 'ar'], true) ? $user->locale : 'fr';
                $translation = trim(EditorialText::present($hadith->translation_i18n, $locale)['text']);
                $reference = trim(implode(' ', array_filter([$hadith->source, $hadith->reference])));
                $message = trim($translation . ($reference !== '' ? " — {$reference}" : ''));
                $message = mb_strlen($message) > 240 ? mb_substr($message, 0, 237) . '…' : $message;

                $title = match ($locale) {
                    'en' => 'Hadith of the day',
                    'ar' => 'حديث اليوم',
                    default => 'Hadith du jour',
                };
                $notification = Notification::query()->create([
                    'user_id' => $user->id,
                    'type' => 'hadith.daily',
                    'channel' => NotificationChannel::IN_APP,
                    'title' => $title,
                    'message' => $message,
                    'data' => [
                        'content_id' => $hadith->id,
                        'display_date' => $today,
                    ],
                    'action_url' => "/{$locale}/ressources/quotidien",
                    'sent_at' => now(),
                ]);

                DeliverPushNotification::dispatch($notification->id);
                $created++;
            }
        });

        $this->info("Notifications créées pour {$created} destinataire(s).");

        return self::SUCCESS;
    }
}
