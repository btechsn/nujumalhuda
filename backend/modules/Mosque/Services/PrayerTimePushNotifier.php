<?php

declare(strict_types=1);

namespace Modules\Mosque\Services;

use Carbon\Carbon;
use Modules\Core\Enums\NotificationChannel;
use Modules\Core\Jobs\DeliverPushNotification;
use Modules\Core\Models\Membership;
use Modules\Core\Models\Notification;
use Modules\Core\Models\NotificationPreference;
use Modules\Core\Models\User;
use Modules\Mosque\Models\PrayerTime;

class PrayerTimePushNotifier
{
    public function notifyCurrentPrayers(?Carbon $now = null): int
    {
        $timezone = (string) config('app.timezone', 'Africa/Dakar');
        $now = ($now ?? Carbon::now($timezone))->copy()->timezone($timezone);
        $lookback = $now->copy()->subMinutes(2);
        $created = 0;

        $prayers = PrayerTime::query()->whereDate('date', $now->toDateString())->get();

        foreach ($prayers as $prayer) {
            $time = $prayer->getEffectiveTime();
            if (!$time) {
                continue;
            }

            $prayerAt = Carbon::parse($prayer->date->format('Y-m-d') . ' ' . $time, $timezone);
            if ($prayerAt->gt($now) || $prayerAt->lt($lookback)) {
                continue;
            }

            $userIds = Membership::query()
                ->where('organization_id', $prayer->organization_id)
                ->where('status', 'active')
                ->pluck('user_id')
                ->merge(NotificationPreference::query()
                    ->where('category', 'prayers')
                    ->where('channel', NotificationChannel::PUSH)
                    ->where('enabled', true)
                    ->pluck('user_id'))
                ->filter()
                ->unique()
                ->values();

            $users = User::query()->whereIn('id', $userIds)->get(['id', 'locale']);
            foreach ($users as $user) {
                $alreadyNotified = Notification::query()
                    ->where('user_id', $user->id)
                    ->where('type', 'prayer.time')
                    ->where('data->prayer_time_id', $prayer->id)
                    ->exists();

                if ($alreadyNotified) {
                    continue;
                }

                $locale = in_array($user->locale, ['fr', 'en', 'ar'], true) ? $user->locale : 'fr';
                [$title, $message] = $this->copy($prayer->prayer_name, $locale);
                $notification = Notification::query()->create([
                    'user_id' => $user->id,
                    'type' => 'prayer.time',
                    'channel' => NotificationChannel::IN_APP,
                    'title' => $title,
                    'message' => $message,
                    'data' => [
                        'prayer_time_id' => $prayer->id,
                        'prayer_name' => $prayer->prayer_name,
                        'prayer_date' => $prayer->date->format('Y-m-d'),
                        'organization_id' => $prayer->organization_id,
                    ],
                    'action_url' => "/{$locale}/mosque/prayer-times",
                    'sent_at' => now(),
                ]);

                DeliverPushNotification::dispatch($notification->id);
                $created++;
            }
        }

        return $created;
    }

    /** @return array{string, string} */
    private function copy(string $prayer, string $locale): array
    {
        $names = [
            'fajr' => ['fr' => 'Fajr', 'en' => 'Fajr', 'ar' => 'الفجر'],
            'dhuhr' => ['fr' => 'Dhuhr', 'en' => 'Dhuhr', 'ar' => 'الظهر'],
            'asr' => ['fr' => 'Asr', 'en' => 'Asr', 'ar' => 'العصر'],
            'maghrib' => ['fr' => 'Maghrib', 'en' => 'Maghrib', 'ar' => 'المغرب'],
            'isha' => ['fr' => 'Isha', 'en' => 'Isha', 'ar' => 'العشاء'],
        ];
        $name = $names[$prayer][$locale] ?? ucfirst($prayer);

        return match ($locale) {
            'en' => ['Prayer time', "It is time for {$name} prayer."],
            'ar' => ['وقت الصلاة', "حان وقت صلاة {$name}."],
            default => ['Heure de prière', "C'est l'heure de la prière de {$name}."],
        };
    }
}
