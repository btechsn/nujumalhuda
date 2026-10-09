<?php

namespace Modules\Education\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'sanad' => $this->sanadPayload(),
            'ijaza_details' => $this->ijaza_details,
            'ijaza_document_url' => $this->ijazaDocumentUrl(),

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

    /**
     * @return array{riwaya?: string, chain: array<int, string>}|null
     */
    private function sanadPayload(): ?array
    {
        $sanad = $this->sanad;

        if (is_string($sanad)) {
            $chain = $this->sanadLines($sanad);

            return $chain === [] ? null : ['chain' => $chain];
        }

        if (! is_array($sanad)) {
            return null;
        }

        $lines = $sanad['chain'] ?? (array_is_list($sanad) ? $sanad : []);
        $chain = [];
        foreach ($lines as $line) {
            if (is_string($line) && trim($line) !== '') {
                $chain[] = trim($line);
            }
        }

        $riwaya = is_string($sanad['riwaya'] ?? null) ? trim($sanad['riwaya']) : '';
        if ($chain === [] && $riwaya === '') {
            return null;
        }

        $payload = ['chain' => $chain];
        if ($riwaya !== '') {
            $payload['riwaya'] = $riwaya;
        }

        return $payload;
    }

    /**
     * @return array<int, string>
     */
    private function sanadLines(string $value): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $value) ?: [];

        return array_values(array_filter(array_map('trim', $lines)));
    }

    private function ijazaDocumentUrl(): ?string
    {
        $details = $this->ijaza_details;
        $path = is_array($details) ? ($details['document'] ?? null) : null;

        if (! is_string($path) || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
