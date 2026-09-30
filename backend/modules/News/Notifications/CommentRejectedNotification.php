<?php

namespace Modules\News\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\News\Models\ArticleComment;

class CommentRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ArticleComment $comment
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $articleTitle = $this->comment->article?->getTitle('fr') ?? 'l’article';

        $mail = (new MailMessage)
            ->subject('Votre commentaire n’a pas été publié — Nujum Al-Huda')
            ->greeting('Salam alaykoum ' . ($notifiable->name ?? '') . ',')
            ->line("Votre commentaire sur « {$articleTitle} » n’a pas été publié.");

        if ($this->comment->moderation_reason) {
            $mail->line('Motif : ' . $this->comment->moderation_reason);
        }

        return $mail
            ->line('Vous pouvez proposer un nouveau commentaire respectueux des règles de la communauté.')
            ->salutation('L’équipe Nujum Al-Huda');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'comment_id' => $this->comment->id,
            'article_id' => $this->comment->article_id,
            'status' => 'rejected',
            'reason' => $this->comment->moderation_reason,
            'message' => 'Votre commentaire n’a pas été publié.',
        ];
    }
}
