<?php

declare(strict_types=1);

namespace Modules\Core\Notifications;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Core\Contracts\NotificationChannel as NotificationChannelContract;
use Modules\Core\Data\NotificationData;
use Modules\Core\Models\User;

class MailNotificationChannel implements NotificationChannelContract
{
    public function send(User $user, NotificationData $notification): bool
    {
        if (!$user->email || !$this->configured()) {
            Log::info('E-mail non envoyé : destinataire ou serveur SMTP absent.');

            return false;
        }

        try {
            Mail::raw($notification->message, function ($message) use ($user, $notification): void {
                $message->to($user->email)->subject($notification->title);
            });
        } catch (\Throwable $exception) {
            Log::warning('E-mail refusé', ['error' => $exception->getMessage()]);

            return false;
        }

        return true;
    }

    public function getName(): string
    {
        return 'email';
    }

    public function isAvailableFor(User $user): bool
    {
        return (bool) $user->email && $this->configured();
    }

    private function configured(): bool
    {
        $host = (string) config('mail.mailers.smtp.host', env('MAIL_HOST'));

        return $host !== '' && $host !== 'smtp.example.com';
    }
}
