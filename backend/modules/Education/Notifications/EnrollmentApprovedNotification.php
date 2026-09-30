<?php

namespace Modules\Education\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Education\Models\Enrollment;

class EnrollmentApprovedNotification extends Notification
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

        return (new MailMessage)
            ->subject('✅ Inscription approuvée - Nujum Al-Huda')
            ->greeting("Salam Alaykoum {$this->enrollment->student_name},")
            ->line('**Félicitations !** Votre inscription a été approuvée.')
            ->line("**Programme** : {$programName}")
            ->line("**Promotion** : {$promotionName}")
            ->line("**Début des cours** : " . $this->enrollment->promotion->start_date->format('d/m/Y'))
            ->line('Vous pouvez maintenant accéder à votre espace étudiant et consulter les informations complémentaires.')
            ->action('Accéder à mon espace', url('/dashboard/enrollments/' . $this->enrollment->id))
            ->line('**Prochaines étapes** :')
            ->line('1. Compléter le paiement des frais de scolarité')
            ->line('2. Télécharger les documents requis')
            ->line('3. Assister à la réunion d\'orientation')
            ->line('En cas de questions, n\'hésitez pas à nous contacter.')
            ->salutation('Cordialement, L\'équipe Nujum Al-Huda');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'enrollment_id' => $this->enrollment->id,
            'promotion_id' => $this->enrollment->promotion_id,
            'status' => 'approved',
            'message' => 'Votre inscription a été approuvée !',
        ];
    }
}
