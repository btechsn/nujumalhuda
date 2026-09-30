<?php

namespace Modules\Live\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LiveStreamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'channel' => [
                'id' => $this->channel->id,
                'slug' => $this->channel->slug,
                'name' => $this->channel->getLocalizedName(),
                'type' => $this->channel->type,
            ],
            'title' => $this->getLocalizedTitle(),
            'description' => $this->getLocalizedDescription(),
            'type' => $this->type,
            'status' => $this->status,
            'scheduled_at' => $this->scheduled_at?->toISOString(),
            'started_at' => $this->started_at?->toISOString(),
            'ended_at' => $this->ended_at?->toISOString(),
            'duration_seconds' => $this->duration_seconds,
            'current_viewers' => $this->isLive() ? $this->getCurrentViewerCount() : 0,
            'peak_viewers' => $this->peak_viewers,
            'total_views' => $this->total_views,
            'chat_messages_count' => $this->chat_messages_count,
            'enable_chat' => $this->enable_chat,
            'enable_reactions' => $this->enable_reactions,
            'is_featured' => $this->is_featured,
            'thumbnail_url' => $this->thumbnail_url,
            'creator' => $this->when($this->creator, function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                ];
            }),
            'stream_urls' => $this->when($this->isLive() || $this->isScheduled(), [
                'whep' => $this->getStreamUrl(),
                'hls' => $this->getHlsUrl(),
            ]),
            'rtmp_ingest' => $this->when($request->user()?->can('manage', $this->resource), [
                'url' => $this->getRtmpIngestUrl(),
                'publish_key' => $this->publish_key,
            ]),
            'recording' => $this->when($this->recording, function () {
                return [
                    'id' => $this->recording->id,
                    'slug' => $this->recording->slug,
                    'status' => $this->recording->status,
                ];
            }),
            'metadata' => $this->metadata,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
