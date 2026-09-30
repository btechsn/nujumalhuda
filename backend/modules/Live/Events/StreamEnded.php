<?php

namespace Modules\Live\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Live\Models\LiveStream;

class StreamEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public LiveStream $stream;

    public function __construct(LiveStream $stream)
    {
        $this->stream = $stream;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('live-streams'),
            new Channel('live-stream.' . $this->stream->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'stream.ended';
    }

    public function broadcastWith(): array
    {
        return [
            'stream_id' => $this->stream->id,
            'status' => $this->stream->status,
            'ended_at' => $this->stream->ended_at?->toISOString(),
            'duration_seconds' => $this->stream->duration_seconds,
            'peak_viewers' => $this->stream->peak_viewers,
            'total_views' => $this->stream->total_views,
            'chat_messages_count' => $this->stream->chat_messages_count,
            'has_recording' => $this->stream->recording()->exists(),
        ];
    }
}
