<?php

namespace Modules\Live\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReactionSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $streamId;
    public string $reaction;
    public ?array $user;

    public function __construct(string $streamId, string $reaction, ?array $user = null)
    {
        $this->streamId = $streamId;
        $this->reaction = $reaction;
        $this->user = $user;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('live-stream.' . $this->streamId . '.reactions'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'reaction.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'stream_id' => $this->streamId,
            'reaction' => $this->reaction,
            'user' => $this->user,
            'timestamp' => now()->toISOString(),
        ];
    }
}
