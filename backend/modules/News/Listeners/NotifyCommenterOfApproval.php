<?php

namespace Modules\News\Listeners;

use Modules\News\Events\CommentApproved;
use Modules\News\Notifications\CommentApprovedNotification;

class NotifyCommenterOfApproval
{
    public function handle(CommentApproved $event): void
    {
        $comment = $event->comment->loadMissing(['user', 'article']);

        $comment->user?->notify(new CommentApprovedNotification($comment));
    }
}
