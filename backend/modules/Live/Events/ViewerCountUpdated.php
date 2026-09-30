<?php

namespace Modules\Live\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Live\Models\LiveStream;

class ViewerCountUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public LiveStream $stream;
    public int $viewerCount;

    public function __construct(LiveStream $stream, int $viewerCount)
    {
        $this->stream = $stream;
        $this->viewerCount = $viewerCount;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('live-stream.' . $this->stream->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'viewer.count.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'stream_id' => $this->stream->id,
            'viewer_count' => $this->viewerCount,
            'peak_viewers' => $this->stream->peak_viewers,
            'timestamp' => now()->toISOString(),
        ];
    }
}
