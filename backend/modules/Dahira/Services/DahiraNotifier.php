<?php

namespace Modules\Dahira\Services;

use Modules\Announcements\Enums\AnnouncementCategory;
use Modules\Announcements\Enums\AnnouncementPriority;
use Modules\Announcements\Enums\AudienceType;
use Modules\Announcements\Models\Announcement;
use Modules\Announcements\Models\AnnouncementAudience;
use Modules\Core\Enums\NotificationChannel;
use Modules\Core\Models\Membership;
use Modules\Core\Models\Notification;
use Modules\Core\Services\NotificationDispatcher;
use Modules\Dahira\Models\ContributionSchedule;
use Modules\Dahira\Models\DahiraGroup;
use Modules\Dahira\Models\Meeting;

class DahiraNotifier
{
    public function announce(DahiraGroup $group, string $title, string $message): Announcement
    {
        $announcement = new Announcement();
        $announcement->setTranslations('title', ['fr' => $title]);
        $announcement->setTranslations('message', ['fr' => $message]);
        $announcement->category = AnnouncementCategory::COMMUNITY;
        $announcement->priority = AnnouncementPriority::NORMAL;
        $announcement->is_active = true;
        $announcement->starts_at = now();
        $announcement->metadata = ['dahira_group_id' => $group->id];
        $announcement->save();

        AnnouncementAudience::create([
            'announcement_id' => $announcement->id,
            'type' => AudienceType::ORGANIZATION,
            'target_id' => $group->organization_id,
        ]);

        $this->notifyMembers($group, 'dahira.announcement', $title, $message, [
            'announcement_id' => $announcement->id,
        ]);

        return $announcement;
    }

    public function convene(Meeting $meeting): int
    {
        $meeting->load('group');
        $count = $this->notifyMembers(
            $meeting->group,
            'dahira.meeting',
            'Réunion : ' . $meeting->title,
            'Convocation le ' . $meeting->starts_at->timezone('Africa/Dakar')->format('d/m/Y H:i')
                . ($meeting->location ? ' — ' . $meeting->location : ''),
            ['meeting_id' => $meeting->id]
        );

        $meeting->update(['convened_at' => now()]);

        return $count;
    }

    public function remindDues(): int
    {
        $schedules = ContributionSchedule::query()
            ->with('membership', 'plan.group')
            ->whereIn('status', ['due', 'overdue'])
            ->where(function ($query) {
                $query->whereNull('reminded_at')->orWhere('reminded_at', '<', now()->subDays(7));
            })
            ->whereDate('due_on', '<=', now()->addDays(7))
            ->get();

        foreach ($schedules as $schedule) {
            Notification::create([
                'user_id' => $schedule->membership->user_id,
                'type' => 'dahira.contribution_due',
                'channel' => NotificationChannel::IN_APP,
                'title' => 'Cotisation du dahira',
                'message' => 'Une cotisation de ' . number_format($schedule->amount_minor, 0, ',', ' ')
                    . ' XOF est due le ' . $schedule->due_on->format('d/m/Y') . '.',
                'data' => ['schedule_id' => $schedule->id],
                'sent_at' => now(),
            ]);

            $sms = Notification::create([
                'user_id' => $schedule->membership->user_id,
                'type' => 'dahira.contribution_due',
                'channel' => NotificationChannel::SMS,
                'title' => 'Cotisation',
                'message' => 'Nujum Al-Huda — cotisation due le ' . $schedule->due_on->format('d/m') . '.',
                'data' => ['schedule_id' => $schedule->id, 'prepared' => true, 'sent' => false],
                'sent_at' => null,
            ]);
            app(NotificationDispatcher::class)->deliver($sms);

            $schedule->update(['reminded_at' => now()]);
        }

        return $schedules->count();
    }

    protected function notifyMembers(DahiraGroup $group, string $type, string $title, string $message, array $data = []): int
    {
        $members = Membership::query()
            ->where('organization_id', $group->organization_id)
            ->where('status', 'active')
            ->get();

        foreach ($members as $member) {
            Notification::create([
                'user_id' => $member->user_id,
                'type' => $type,
                'channel' => NotificationChannel::IN_APP,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'sent_at' => now(),
            ]);
        }

        return $members->count();
    }
}
