<?php

namespace Modules\Resources\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LibraryResource extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'slug', 'title_i18n', 'description_i18n', 'author', 'tradition', 'kind',
        'language', 'file_path', 'external_url', 'file_size_bytes', 'duration_seconds',
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

    public function localized(string $field, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $value = $this->{$field} ?? [];

        return $value[$locale] ?? $value['fr'] ?? '';
    }
}
