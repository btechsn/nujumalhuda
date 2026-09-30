<?php

namespace Modules\Live\Console\Commands;

use Illuminate\Console\Command;
use Modules\Live\Models\LiveStream;

class ArchiveOldStreamsCommand extends Command
{
    protected $signature = 'live:archive-old {--days=30 : Number of days after which to archive streams}';

    protected $description = 'Archive old ended streams';

    public function handle(): int
    {
        $days = $this->option('days');
        $cutoffDate = now()->subDays($days);

        $streams = LiveStream::where('status', 'ended')
            ->where('ended_at', '<', $cutoffDate)
            ->get();

        if ($streams->isEmpty()) {
            $this->info("No streams to archive (older than {$days} days)");
            return self::SUCCESS;
        }

        $count = 0;
        foreach ($streams as $stream) {
            $stream->update(['status' => 'archived']);
            $count++;
            $this->line("Archived: {$stream->getLocalizedTitle()}");
        }

        $this->info("Successfully archived {$count} stream(s)");
        return self::SUCCESS;
    }
}
