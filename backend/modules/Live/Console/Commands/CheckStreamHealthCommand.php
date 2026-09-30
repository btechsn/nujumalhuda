<?php

namespace Modules\Live\Console\Commands;

use Illuminate\Console\Command;
use Modules\Live\Models\LiveStream;
use Modules\Live\Models\StreamAnalytic;
use Modules\Live\Services\MediaMtxService;

class CheckStreamHealthCommand extends Command
{
    protected $signature = 'live:check-health';

    protected $description = 'Check health of live streams and collect analytics';

    public function handle(MediaMtxService $mediaMtxService): int
    {
        $liveStreams = LiveStream::where('status', 'live')->get();

        if ($liveStreams->isEmpty()) {
            $this->info('No live streams currently active');
            return self::SUCCESS;
        }

        foreach ($liveStreams as $stream) {
            $this->info("Checking stream: {$stream->getLocalizedTitle()}");

            $health = $mediaMtxService->getStreamHealth($stream);

            // Record analytics
            StreamAnalytic::create([
                'stream_id' => $stream->id,
                'recorded_at' => now(),
                'concurrent_viewers' => $stream->getCurrentViewerCount(),
                'bitrate_kbps' => $health['bitrate'] ?? null,
                'metadata' => $health,
            ]);

            $this->line("  - Viewers: {$stream->getCurrentViewerCount()}");
            $this->line("  - Ready: " . ($health['is_ready'] ? 'Yes' : 'No'));
        }

        $this->info('Health check completed');
        return self::SUCCESS;
    }
}
