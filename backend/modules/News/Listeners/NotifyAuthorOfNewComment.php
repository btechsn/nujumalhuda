<?php

namespace Modules\News\Listeners;

use Modules\News\Events\CommentPosted;
use Modules\News\Notifications\NewCommentNotification;

class NotifyAuthorOfNewComment
{
    public function handle(CommentPosted $event): void
    {
        $comment = $event->comment->loadMissing(['article.author', 'user']);
        $author = $comment->article?->author;

        if (! $author || $author->id === $comment->user_id) {
            return;
        }

        if (in_array($comment->status, ['spam'], true)) {
            return;
        }

        try {
            $author->notify(new NewCommentNotification($comment));
        } catch (\Throwable) {
            // Un échec de notification ne doit pas empêcher d'enregistrer le commentaire.
        }
    }
}
