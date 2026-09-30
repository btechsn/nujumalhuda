<?php

namespace Modules\Education\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'course_id',
        'title_i18n',
        'content_i18n',
        'sequence',
        'duration_minutes',
        'type',
        'materials',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'title_i18n' => 'array',
        'content_i18n' => 'array',
        'materials' => 'array',
        'is_active' => 'boolean',
        'sequence' => 'integer',
        'duration_minutes' => 'integer',
    ];

    /**
     * Relations
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Helpers
     */
    public function getTitle(string $locale = 'fr'): ?string
    {
        return $this->title_i18n[$locale] ?? $this->title_i18n['fr'] ?? null;
    }

    public function getContent(string $locale = 'fr'): ?string
    {
        return $this->content_i18n[$locale] ?? $this->content_i18n['fr'] ?? null;
    }
}
