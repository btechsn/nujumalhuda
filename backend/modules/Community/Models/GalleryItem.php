<?php

namespace Modules\Community\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GalleryItem extends Model
{
    use HasUlids;

    protected $fillable = [
        'kind',
        'caption_i18n',
        'event_name',
        'media_url',
        'media_path',
        'thumbnail_url',
        'thumbnail_path',
        'taken_on',
        'is_public',
    ];

    protected $casts = [
        'caption_i18n' => 'array',
        'taken_on' => 'date',
        'is_public' => 'boolean',
    ];

    public function publicMediaUrl(): ?string
    {
        return $this->storedFileUrl($this->media_path) ?? $this->plainUrl($this->media_url);
    }

    public function publicThumbnailUrl(): ?string
    {
        return $this->storedFileUrl($this->thumbnail_path) ?? $this->plainUrl($this->thumbnail_url);
    }

    private function storedFileUrl(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    private function plainUrl(?string $url): ?string
    {
        $url = trim((string) $url);

        return $url === '' ? null : $url;
    }
}
