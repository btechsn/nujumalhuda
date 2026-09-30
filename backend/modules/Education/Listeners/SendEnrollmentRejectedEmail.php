<?php

namespace Modules\Education\Listeners;

use Modules\Education\Events\EnrollmentRejected;
use Modules\Education\Notifications\EnrollmentRejectedNotification;
use Illuminate\Support\Facades\Notification;

class SendEnrollmentRejectedEmail
{
    /**
     * Handle the event.
     */
    public function handle(EnrollmentRejected $event): void
    {
        // Créer un "notifiable" à partir de l'email de l'étudiant
        $notifiable = new class($event->enrollment->student_email, $event->enrollment->student_name) {
            public function __construct(
                public string $email,
                public string $name
            ) {}

            public function routeNotificationForMail()
            {
                return $this->email;
            }

            public function routeNotificationForDatabase()
            {
                return null; // Pas de stockage DB pour les non-users
            }
        };

        Notification::send($notifiable, new EnrollmentRejectedNotification($event->enrollment));
    }
}
