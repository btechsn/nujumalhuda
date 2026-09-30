<?php

namespace Modules\Education\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Education\Models\Enrollment;

class EnrollmentRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Enrollment $enrollment
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $programName = $this->enrollment->promotion->program->name_i18n['fr'] ?? 'Programme';
        $promotionName = $this->enrollment->promotion->name_i18n['fr'] ?? 'Promotion';

        $mail = (new MailMessage)
            ->subject('Inscription - Nujum Al-Huda')
            ->greeting("Salam Alaykoum {$this->enrollment->student_name},")
            ->line("Merci pour votre intérêt pour le programme **{$programName}** (Promotion : {$promotionName}).")
            ->line('Malheureusement, nous ne pouvons pas donner suite à votre inscription pour le moment.');

        // Ajouter la raison si elle existe
        if ($this->enrollment->rejection_reason) {
            $mail->line("**Raison** : {$this->enrollment->rejection_reason}");
        }

        $mail->line('Nous vous encourageons à postuler à nouveau lors de la prochaine session de recrutement.')
            ->line('N\'hésitez pas à nous contacter si vous avez des questions.')
            ->salutation('Cordialement, L\'équipe Nujum Al-Huda');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'enrollment_id' => $this->enrollment->id,
            'promotion_id' => $this->enrollment->promotion_id,
            'status' => 'rejected',
            'message' => 'Votre inscription n\'a pas été approuvée.',
            'reason' => $this->enrollment->rejection_reason,
        ];
    }
}
