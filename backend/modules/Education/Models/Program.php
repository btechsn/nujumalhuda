<?php

namespace Modules\Education\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\Organization;

class Program extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name_i18n',
        'description_i18n',
        'objectives_i18n',
        'type',
        'level',
        'duration_weeks',
        'hours_per_week',
        'tuition_amount_minor',
        'registration_amount_minor',
        'currency',
        'min_age',
        'max_age',
        'prerequisite_program_id',
        'is_active',
        'is_featured',
        'display_order',
        'metadata',
    ];

    protected $casts = [
        'name_i18n' => 'array',
        'description_i18n' => 'array',
        'objectives_i18n' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'tuition_amount_minor' => 'integer',
        'registration_amount_minor' => 'integer',
        'duration_weeks' => 'integer',
        'hours_per_week' => 'integer',
        'min_age' => 'integer',
        'max_age' => 'integer',
        'display_order' => 'integer',
    ];

    /**
     * Relations
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function prerequisiteProgram(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'prerequisite_program_id');
    }

    public function dependentPrograms(): HasMany
    {
        return $this->hasMany(Program::class, 'prerequisite_program_id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name_i18n->fr');
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

    public function getTuitionAmount(): float
    {
        return $this->tuition_amount_minor ? $this->tuition_amount_minor / 1 : 0;
    }

    public function getRegistrationAmount(): float
    {
        return $this->registration_amount_minor ? $this->registration_amount_minor / 1 : 0;
    }
}
