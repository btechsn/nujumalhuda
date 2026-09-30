<?php

namespace Modules\News\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\News\Events\CommentApproved;
use Modules\News\Events\CommentPosted;
use Modules\News\Events\CommentRejected;
use Modules\News\Listeners\NotifyAuthorOfNewComment;
use Modules\News\Listeners\NotifyCommenterOfApproval;
use Modules\News\Listeners\NotifyCommenterOfRejection;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        CommentPosted::class => [
            NotifyAuthorOfNewComment::class,
        ],
        CommentApproved::class => [
            NotifyCommenterOfApproval::class,
        ],
        CommentRejected::class => [
            NotifyCommenterOfRejection::class,
        ],
    ];
}
