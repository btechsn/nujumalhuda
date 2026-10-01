<?php

namespace Modules\Mosque\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KhutbaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title_i18n,
            'summary' => $this->summary_i18n,
            'content' => $this->content_i18n,
            'date' => $this->date?->format('Y-m-d'),
            'time' => $this->time?->format('H:i'),
            'speaker' => $this->when($this->speaker, [
                'id' => $this->speaker?->id,
                'name' => $this->speaker?->name,
            ]) ?? ['name' => $this->speaker_name],
            'audio_url' => $this->audio?->url(),
            'video_url' => $this->video?->url(),
            'youtube_url' => $this->youtube_url,
            'key_points' => $this->key_points,
            'references' => $this->references,
            'published_at' => $this->published_at?->toIso8601String(),
            'related_live' => $this->when(
                isset($this->related_live) && $this->related_live,
                function () {
                    $live = $this->related_live;

                    return [
                        'id' => $live->id,
                        'title' => method_exists($live, 'getLocalizedTitle') ? $live->getLocalizedTitle() : ($live->title ?? null),
                        'status' => $live->status,
                        'thumbnail_url' => $live->thumbnail_url,
                        'scheduled_at' => $live->scheduled_at?->toIso8601String(),
                        'recording' => $live->recording ? [
                            'id' => $live->recording->id,
                            'slug' => $live->recording->slug,
                            'status' => $live->recording->status,
                        ] : null,
                    ];
                }
            ),
        ];
    }
}
