<?php

declare(strict_types=1);

namespace Modules\Announcements\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Announcements\Enums\AudienceType;
use Modules\Announcements\Models\Announcement;
use Modules\Announcements\Models\AnnouncementAudience;
use Modules\Core\Enums\NotificationChannel;
use Modules\Core\Jobs\DeliverPushNotification;
use Modules\Core\Models\Membership;
use Modules\Core\Models\Notification;
use Modules\Core\Models\User;

class AnnouncementPushNotifier
{
    public function notify(Announcement $announcement): int
    {
        if (!$announcement->isVisible()) {
            return 0;
        }

        $announcement->loadMissing('audiences');
        $userIds = $this->recipientIds($announcement);
        if ($userIds->isEmpty()) {
            return 0;
        }

        $sent = 0;
        $users = User::query()->whereIn('id', $userIds)->get(['id', 'locale']);

        foreach ($users as $user) {
            $existing = Notification::query()
                ->where('user_id', $user->id)
                ->where('type', 'announcement.published')
                ->where('data->announcement_id', $announcement->id)
                ->exists();

            if ($existing) {
                continue;
            }

            $locale = in_array($user->locale, ['fr', 'en', 'ar'], true) ? $user->locale : 'fr';
            $notification = Notification::query()->create([
                'user_id' => $user->id,
                'type' => 'announcement.published',
                'channel' => NotificationChannel::IN_APP,
                'title' => $announcement->getTranslation('title', $locale) ?: 'Annonce',
                'message' => $announcement->getTranslation('message', $locale) ?: '',
                'data' => ['announcement_id' => $announcement->id],
                'action_url' => $announcement->action_url ?: "/{$locale}/centre/annonces",
                'sent_at' => now(),
            ]);

            DeliverPushNotification::dispatch($notification->id);
            $sent++;
        }

        return $sent;
    }

    /** @return Collection<int, string> */
    private function recipientIds(Announcement $announcement): Collection
    {
        $userIds = collect();

        foreach ($announcement->audiences as $audience) {
            $type = $audience->type instanceof AudienceType
                ? $audience->type
                : AudienceType::tryFrom((string) $audience->type);

            $ids = match ($type) {
                AudienceType::PUBLIC => User::query()->pluck('id'),
                AudienceType::MEMBERS => Membership::query()->where('status', 'active')->pluck('user_id'),
                AudienceType::ROLE => $audience->target_id
                    ? Membership::query()->where('status', 'active')->where('role_id', $audience->target_id)->pluck('user_id')
                    : collect(),
                AudienceType::ORGANIZATION => $audience->target_id
                    ? Membership::query()->where('status', 'active')->where('organization_id', $audience->target_id)->pluck('user_id')
                    : collect(),
                AudienceType::COHORT => $audience->target_id
                    ? DB::table('enrollments')
                        ->where('promotion_id', $audience->target_id)
                        ->whereIn('status', ['approved', 'active', 'completed'])
                        ->pluck('user_id')
                    : collect(),
                default => collect(),
            };

            $userIds = $userIds->merge($ids);
        }

        return $userIds->filter()->unique()->values();
    }
}
