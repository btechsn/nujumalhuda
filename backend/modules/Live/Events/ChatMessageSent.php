<?php

namespace Modules\Live\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Live\Models\LiveChatMessage;

class ChatMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public LiveChatMessage $message;

    public function __construct(LiveChatMessage $message)
    {
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('live-stream.' . $this->message->stream_id . '.chat'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.message';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'stream_id' => $this->message->stream_id,
            'message' => $this->message->message,
            'type' => $this->message->type,
            'user' => $this->message->user ? [
                'id' => $this->message->user->id,
                'name' => $this->message->user->name,
                'avatar' => $this->message->user->avatar ?? null,
            ] : [
                'id' => null,
                'name' => 'Invité',
                'avatar' => null,
            ],
            'created_at' => $this->message->created_at->toISOString(),
        ];
    }
}
