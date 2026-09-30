<?php

namespace Modules\News\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\News\Models\ArticleComment;

class CommentPosted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ArticleComment $comment
    ) {}
}
