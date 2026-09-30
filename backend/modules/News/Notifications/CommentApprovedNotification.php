<?php

namespace Modules\News\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\News\Models\ArticleComment;

class CommentApprovedNotification extends Notification
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
        $slug = $this->comment->article?->slug;

        $mail = (new MailMessage)
            ->subject('Votre commentaire est publié — Nujum Al-Huda')
            ->greeting('Salam alaykoum ' . ($notifiable->name ?? '') . ',')
            ->line("Votre commentaire sur « {$articleTitle} » a été approuvé et est maintenant visible.");

        if ($slug) {
            $frontend = rtrim((string) config('app.frontend_url', config('app.url')), '/');
            $mail->action('Voir l’article', $frontend . '/fr/news/' . $slug);
        }

        return $mail->salutation('L’équipe Nujum Al-Huda');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'comment_id' => $this->comment->id,
            'article_id' => $this->comment->article_id,
            'status' => 'approved',
            'message' => 'Votre commentaire a été publié.',
        ];
    }
}
