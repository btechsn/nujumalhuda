<?php

namespace Modules\News\Listeners;

use Modules\News\Events\CommentRejected;
use Modules\News\Notifications\CommentRejectedNotification;

class NotifyCommenterOfRejection
{
    public function handle(CommentRejected $event): void
    {
        $comment = $event->comment->loadMissing(['user', 'article']);

        $comment->user?->notify(new CommentRejectedNotification($comment));
    }
}
