<?php

namespace Modules\Live\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Support\RetentionPolicy;
use Modules\Live\Services\LiveChatService;

class CleanupExpiredChatsCommand extends Command
{
    protected $signature = 'live:cleanup-chats {--days= : Nombre de jours conservés. Par défaut, la durée du §8.3.}';

    protected $description = 'Clean up old chat messages from archived streams';

    public function handle(LiveChatService $chatService): int
    {
        $days = $this->option('days');
        $days = ($days === null || $days === '') ? RetentionPolicy::LIVE_CHAT_DAYS : (int) $days;

        $this->info("Cleaning up chat messages older than {$days} days...");

        $deleted = $chatService->cleanupOldMessages($days);

        $this->info("Successfully deleted {$deleted} chat message(s)");
        return self::SUCCESS;
    }
}
