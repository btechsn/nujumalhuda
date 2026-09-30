<?php

namespace Modules\Mosque\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Mosque\Models\EventRegistration;
use Modules\Mosque\Models\MosqueEvent;

class EventConfirmationMailer
{
    public function send(EventRegistration $registration, MosqueEvent $event): bool
    {
        $host = (string) config('mail.mailers.smtp.host');
        if ($registration->participant_email === null || $host === '' || $host === 'smtp.example.com') {
            Log::info('Confirmation d\'événement non envoyée : SMTP absent.');

            return false;
        }

        $title = $event->title_i18n['fr'] ?? 'Événement';
        $when = $event->start_at?->timezone('Africa/Dakar')->format('d/m/Y H:i');
        $body = "Salam alaykoum {$registration->participant_name},\n\n"
            . "Votre inscription à « {$title} » est enregistrée.\n"
            . "Date : {$when}\n"
            . "Code : {$registration->confirmation_code}\n"
            . "Participants : {$registration->number_of_attendees}\n\n"
            . "Nujum Al-Huda Institute Center";

        try {
            Mail::raw($body, function ($message) use ($registration, $title): void {
                $message->to($registration->participant_email)->subject('Inscription — ' . $title);
            });
        } catch (\Throwable $exception) {
            Log::warning('Confirmation d\'événement refusée', ['error' => $exception->getMessage()]);

            return false;
        }

        return true;
    }
}
