<?php

declare(strict_types=1);

namespace Modules\Core\Services;

use Modules\Core\Enums\NotificationChannel as Channel;
use Modules\Core\Models\Notification;
use Modules\Core\Models\User;
use Modules\Core\Notifications\MailNotificationChannel;
use Modules\Core\Notifications\OrangeSmsChannel;
use Modules\Core\Notifications\WebPushChannel;

class NotificationDispatcher
{
    public function __construct(
        private readonly OrangeSmsChannel $sms,
        private readonly MailNotificationChannel $mail,
        private readonly WebPushChannel $push,
    ) {
    }

    public function deliver(Notification $notification): bool
    {
        $user = User::query()->find($notification->user_id);
        if (!$user) {
            return false;
        }

        $data = new \Modules\Core\Data\NotificationData(
            user_id: $user->id,
            type: $notification->type,
            channel: $notification->channel,
            title: $notification->title,
            message: $notification->message,
            data: $notification->data,
            action_url: $notification->action_url,
        );

        $sent = match ($notification->channel) {
            Channel::SMS => $this->sms->send($user, $data),
            Channel::EMAIL => $this->mail->send($user, $data),
            Channel::PUSH => $this->push->send($user, $data),
            Channel::IN_APP => true,
        };

        if ($sent && $notification->sent_at === null && $notification->channel !== Channel::IN_APP) {
            $notification->update([
                'sent_at' => now(),
                'data' => array_merge($notification->data ?? [], ['sent' => true]),
            ]);
        }

        return $sent;
    }
}
