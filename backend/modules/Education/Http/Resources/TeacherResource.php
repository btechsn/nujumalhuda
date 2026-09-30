<?php

namespace Modules\Education\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $availability = is_array($this->availability) ? $this->availability : [];

        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,

            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'title' => $availability['title_i18n'] ?? null,

            'bio' => $this->bio_i18n,
            'specialties' => $this->specialties_i18n,
            'qualifications' => $this->qualifications_i18n,

            'has_ijaza' => (bool) $this->has_ijaza,
            'sanad' => $this->sanad,
            'ijaza_details' => $this->ijaza_details,

            'availability' => $availability,
            'is_available' => (bool) $this->is_available,

            'photo_url' => $this->photo?->url()
                ?? ($availability['photo'] ?? null),
            'youtube_channel' => $this->youtube_channel,
            'facebook_page' => $this->facebook_page,

            'stats' => [
                'students_count' => $this->students_count,
                'courses_taught' => $this->courses_taught,
                'total_sessions' => $this->total_sessions,
            ],

            'is_featured' => (bool) $this->is_featured,
            'display_order' => $this->display_order,

            'organization' => $this->whenLoaded('organization'),

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
