<?php

namespace Modules\Education\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\Media;
use Modules\Core\Models\Organization;
use Modules\Core\Models\User;

class Teacher extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'id', // Même que user_id
        'organization_id',
        'bio_i18n',
        'specialties_i18n',
        'qualifications_i18n',
        'sanad',
        'has_ijaza',
        'ijaza_details',
        'availability',
        'is_available',
        'photo_media_id',
        'youtube_channel',
        'facebook_page',
        'students_count',
        'courses_taught',
        'total_sessions',
        'is_featured',
        'display_order',
    ];

    protected $casts = [
        'bio_i18n' => 'array',
        'specialties_i18n' => 'array',
        'qualifications_i18n' => 'array',
        'sanad' => 'array',
        'ijaza_details' => 'array',
        'availability' => 'array',
        'has_ijaza' => 'boolean',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'students_count' => 'integer',
        'courses_taught' => 'integer',
        'total_sessions' => 'integer',
        'display_order' => 'integer',
    ];

    /**
     * Relations
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'photo_media_id');
    }

    /**
     * Scopes
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('created_at');
    }

    /**
     * Helpers
     */
    public function getBio(string $locale = 'fr'): ?string
    {
        return $this->bio_i18n[$locale] ?? $this->bio_i18n['fr'] ?? null;
    }

    public function getSpecialties(string $locale = 'fr'): array
    {
        return $this->specialties_i18n[$locale] ?? $this->specialties_i18n['fr'] ?? [];
    }

    public function getQualifications(string $locale = 'fr'): array
    {
        return $this->qualifications_i18n[$locale] ?? $this->qualifications_i18n['fr'] ?? [];
    }
}
