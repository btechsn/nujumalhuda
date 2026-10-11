<?php

declare(strict_types=1);

namespace Modules\Live\Services;

use Modules\Live\Models\LiveStream;

class SyncFinishedYoutubeLives
{
    public function __construct(private readonly YoutubeBroadcastStatus $youtube) {}

    public function run(): int
    {
        $closed = 0;

        $streams = LiveStream::query()
            ->where('status', 'live')
            ->whereNotNull('external_url')
            ->get();

        foreach ($streams as $stream) {
            $videoId = $this->youtube->videoId($stream);

            if ($videoId === null) {
                continue;
            }

            $result = $this->youtube->inspect($videoId);

            if ($result['state'] === 'live') {
                $stream->recordYoutubeBroadcast($result, false);

                continue;
            }

            if ($result['state'] !== 'ended') {
                continue;
            }

            $stream->recordYoutubeBroadcast($result, true);
            $closed++;
        }

        $this->fillMissingStats();

        return $closed;
    }

    private function fillMissingStats(): void
    {
        $streams = LiveStream::query()
            ->whereIn('status', ['ended', 'archived'])
            ->whereNotNull('external_url')
            ->where(function ($query) {
                $query->whereNull('duration_seconds')->orWhere('duration_seconds', 0);
            })
            ->limit(5)
            ->get();

        foreach ($streams as $stream) {
            $videoId = $this->youtube->videoId($stream);

            if ($videoId === null) {
                continue;
            }

            $result = $this->youtube->inspect($videoId);

            if ($result['state'] !== 'ended') {
                continue;
            }

            $stream->recordYoutubeBroadcast($result, false);
        }
    }
}
