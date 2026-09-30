<?php

namespace Modules\Education\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'program_id',
        'name_i18n',
        'description_i18n',
        'objectives_i18n',
        'code',
        'sequence',
        'duration_hours',
        'syllabus_i18n',
        'resources',
        'is_active',
    ];

    protected $casts = [
        'name_i18n' => 'array',
        'description_i18n' => 'array',
        'objectives_i18n' => 'array',
        'syllabus_i18n' => 'array',
        'resources' => 'array',
        'is_active' => 'boolean',
        'sequence' => 'integer',
        'duration_hours' => 'integer',
    ];

    /**
     * Relations
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('sequence');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
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

    /**
     * Helpers
     */
    public function getName(string $locale = 'fr'): ?string
    {
        return $this->name_i18n[$locale] ?? $this->name_i18n['fr'] ?? null;
    }

    public function getDescription(string $locale = 'fr'): ?string
    {
        return $this->description_i18n[$locale] ?? $this->description_i18n['fr'] ?? null;
    }
}
