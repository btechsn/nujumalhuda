<?php

namespace Modules\News\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\News\Models\Article;

class ArticlePublished
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Article $article
    ) {}
}
