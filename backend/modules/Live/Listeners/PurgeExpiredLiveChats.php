<?php

declare(strict_types=1);

namespace Modules\Live\Listeners;

use Modules\Core\Events\PersonalDataPurgeRunning;
use Modules\Core\Support\RetentionPolicy;
use Modules\Live\Services\LiveChatService;

class PurgeExpiredLiveChats
{
    public function __construct(private readonly LiveChatService $chats)
    {
    }

    public function handle(PersonalDataPurgeRunning $event): void
    {
        $event->purged['live_chat_messages'] = $this->chats->cleanupOldMessages(RetentionPolicy::LIVE_CHAT_DAYS);
    }
}
