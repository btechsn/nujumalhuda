<?php

namespace Modules\News\Notifications;

use Filament\Facades\Filament;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\News\Models\ArticleComment;

class NewCommentNotification extends Notification
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
        $articleTitle = $this->comment->article?->getTitle('fr') ?? 'un article';
        $authorName = $this->comment->user?->name ?? 'Un lecteur';

        return (new MailMessage)
            ->subject('Nouveau commentaire à modérer — Nujum Al-Huda')
            ->greeting('Salam alaykoum,')
            ->line("{$authorName} a publié un commentaire sur « {$articleTitle} ».")
            ->line('Le commentaire est en attente de modération avant d’être visible publiquement.')
            ->line('Extrait :')
            ->line('« ' . mb_strimwidth($this->comment->content, 0, 280, '…') . ' »')
            ->action('Ouvrir l’administration', Filament::getPanel('admin')->getUrl())
            ->salutation('L’équipe Nujum Al-Huda');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'comment_id' => $this->comment->id,
            'article_id' => $this->comment->article_id,
            'status' => $this->comment->status,
            'message' => 'Nouveau commentaire en attente de modération.',
        ];
    }
}
