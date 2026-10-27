<?php

declare(strict_types=1);

namespace Modules\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Enums\NotificationChannel;
use Modules\Core\Models\Notification;
use Modules\Core\Services\NotificationDispatcher;

class DeliverPushNotification implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [30, 120, 300];

    public function __construct(public readonly string $notificationId)
    {
    }

    public function handle(NotificationDispatcher $dispatcher): void
    {
        $notification = Notification::query()->find($this->notificationId);
        if (!$notification || ($notification->data['push_sent'] ?? false)) {
            return;
        }

        if ($dispatcher->deliver($notification, NotificationChannel::PUSH)) {
            $notification->forceFill([
                'data' => array_merge($notification->data ?? [], ['push_sent' => true]),
            ])->save();
        }
    }
}
