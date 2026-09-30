<?php

namespace Modules\Live\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LiveChatMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'stream_id' => $this->stream_id,
            'message' => $this->message,
            'type' => $this->type,
            'status' => $this->status,
            'user' => $this->when($this->user, function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'avatar' => $this->user->avatar ?? null,
                ];
            }),
            'is_spam' => $this->isSpam(),
            'spam_score' => $this->when($request->user()?->can('moderate', $this->resource), $this->spam_score),
            'spam_flags' => $this->when($request->user()?->can('moderate', $this->resource), $this->spam_flags),
            'moderation' => $this->when($this->moderated_by, [
                'moderated_by' => $this->moderator?->name,
                'moderated_at' => $this->moderated_at?->toISOString(),
                'reason' => $this->moderation_reason,
            ]),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
