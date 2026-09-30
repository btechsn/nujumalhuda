<?php

namespace Modules\Live\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VodRecordingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->getLocalizedTitle(),
            'description' => $this->getLocalizedDescription(),
            'channel' => [
                'id' => $this->stream->channel->id,
                'slug' => $this->stream->channel->slug,
                'name' => $this->stream->channel->getLocalizedName(),
            ],
            'hls_url' => $this->hls_url,
            'mp4_url' => $this->when($this->is_downloadable, $this->mp4_url),
            'duration_seconds' => $this->duration_seconds,
            'formatted_duration' => $this->getFormattedDuration(),
            'file_size' => $this->getFormattedFileSize(),
            'resolution' => $this->resolution,
            'bitrate' => $this->bitrate,
            'status' => $this->status,
            'views_count' => $this->views_count,
            'is_public' => $this->is_public,
            'is_downloadable' => $this->is_downloadable,
            'thumbnail_url' => $this->thumbnail_url,
            'chapters' => $this->chapters,
            'published_at' => $this->published_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
