<?php

namespace Modules\Live\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Live\Models\LiveStream;

class StreamStarted implements ShouldBroadcast
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
        return 'stream.started';
    }

    public function broadcastWith(): array
    {
        return [
            'stream_id' => $this->stream->id,
            'channel_id' => $this->stream->channel_id,
            'title' => $this->stream->title,
            'status' => $this->stream->status,
            'started_at' => $this->stream->started_at?->toISOString(),
            'whep_url' => $this->stream->getStreamUrl(),
            'hls_url' => $this->stream->getHlsUrl(),
            'enable_chat' => $this->stream->enable_chat,
            'enable_reactions' => $this->stream->enable_reactions,
        ];
    }
}
