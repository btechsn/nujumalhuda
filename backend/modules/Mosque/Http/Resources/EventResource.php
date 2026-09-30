<?php

namespace Modules\Mosque\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title_i18n,
            'description' => $this->description_i18n,
            'type' => $this->type,
            'start_at' => $this->start_at?->toIso8601String(),
            'end_at' => $this->end_at?->toIso8601String(),
            'location' => $this->location,
            'location_details' => $this->location_details,
            'speaker' => $this->when($this->speaker, [
                'id' => $this->speaker?->id,
                'name' => $this->speaker?->name,
            ]) ?? ['name' => $this->speaker_name],
            'capacity' => $this->capacity,
            'registered_count' => $this->registered_count,
            'can_register' => $this->canRegister(),
            'is_finished' => $this->isFinished(),
            'is_full' => $this->isFull(),
            'image_url' => $this->image?->url,
            'youtube_url' => $this->youtube_url,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
        ];
    }
}
