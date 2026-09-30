<?php

namespace Modules\Live\Observers;

use Modules\Live\Models\LiveStream;
use Modules\Live\Services\MediaMtxService;

class LiveStreamObserver
{
    protected MediaMtxService $mediaMtxService;

    public function __construct(MediaMtxService $mediaMtxService)
    {
        $this->mediaMtxService = $mediaMtxService;
    }

    public function creating(LiveStream $stream): void
    {
        // Generate publish key if not set
        if (empty($stream->publish_key)) {
            $stream->publish_key = $this->mediaMtxService->generatePublishKey();
        }

        // Set default status
        if (empty($stream->status)) {
            $stream->status = 'scheduled';
        }
    }

    public function updated(LiveStream $stream): void
    {
        // Update peak viewers when stream is live
        if ($stream->isLive() && $stream->isDirty('total_views')) {
            $currentViewers = $stream->getCurrentViewerCount();
            if ($currentViewers > $stream->peak_viewers) {
                $stream->updateQuietly(['peak_viewers' => $currentViewers]);
            }
        }
    }

    public function deleted(LiveStream $stream): void
    {
        // Clean up associated resources
        // Sessions, chat messages, etc. will cascade delete via foreign keys
    }
}
