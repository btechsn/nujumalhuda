<?php

namespace Modules\Live\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Support\EditorialText;

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
            'title' => EditorialText::present(is_array($this->title) ? $this->title : null, app()->getLocale())['text'],
            'title_fell_back' => EditorialText::present(is_array($this->title) ? $this->title : null, app()->getLocale())['fell_back'],
            'description' => $this->getLocalizedDescription(),
            'description_fell_back' => EditorialText::present(is_array($this->description) ? $this->description : null, app()->getLocale())['fell_back'],
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
            'thumbnail_url' => $this->publicThumbnailUrl(),
            'external_url' => $this->external_url,
            'embed_url' => $this->getEmbedUrl(),
            'creator' => $this->when($this->creator, function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                ];
            }),
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
