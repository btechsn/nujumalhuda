<?php

namespace Modules\Live\Console\Commands;

use Illuminate\Console\Command;
use Modules\Live\Services\LiveChatService;

class CleanupExpiredChatsCommand extends Command
{
    protected $signature = 'live:cleanup-chats {--days=30 : Number of days to keep chat messages}';

    protected $description = 'Clean up old chat messages from archived streams';

    public function handle(LiveChatService $chatService): int
    {
        $days = $this->option('days');

        $this->info("Cleaning up chat messages older than {$days} days...");

        $deleted = $chatService->cleanupOldMessages($days);

        $this->info("Successfully deleted {$deleted} chat message(s)");
        return self::SUCCESS;
    }
}
