<?php

namespace Modules\Resources\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class LibraryResource extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'slug', 'title_i18n', 'description_i18n', 'author', 'tradition', 'kind',
        'language', 'file_path', 'external_url', 'cover_path', 'file_size_bytes', 'duration_seconds',
        'download_count', 'is_public', 'display_order',
    ];

    protected $casts = [
        'title_i18n' => 'array',
        'description_i18n' => 'array',
        'is_public' => 'boolean',
        'download_count' => 'integer',
        'duration_seconds' => 'integer',
        'file_size_bytes' => 'integer',
        'display_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (LibraryResource $item): void {
            $path = trim((string) $item->file_path);

            if ($path === '' || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return;
            }

            $absolute = Storage::disk('public')->path($path);

            if (is_file($absolute)) {
                $item->file_size_bytes = filesize($absolute) ?: null;
            }
        });
    }

    public function downloadUrl(): ?string
    {
        $path = trim((string) $this->file_path);

        if ($path !== '') {
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }

            return Storage::disk('public')->url(ltrim($path, '/'));
        }

        $url = trim((string) $this->external_url);

        return $url === '' ? null : $url;
    }

    public function coverUrl(): ?string
    {
        $path = trim((string) $this->cover_path);

        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    public function localized(string $field, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $value = $this->{$field} ?? [];

        return $value[$locale] ?? $value['fr'] ?? '';
    }
}
