<?php

namespace Modules\Mosque\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\Media;
use Modules\Core\Models\Organization;
use Modules\Core\Models\User;
use Modules\Mosque\Models\EventRegistration;

class MosqueEvent extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'title_i18n',
        'description_i18n',
        'type',
        'start_at',
        'end_at',
        'is_recurring',
        'recurrence_pattern',
        'location',
        'location_details',
        'speaker_id',
        'speaker_name',
        'capacity',
        'requires_registration',
        'registered_count',
        'image_media_id',
        'youtube_url',
        'status',
        'is_featured',
    ];

    protected $casts = [
        'title_i18n' => 'array',
        'description_i18n' => 'array',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_recurring' => 'boolean',
        'requires_registration' => 'boolean',
        'is_featured' => 'boolean',
        'capacity' => 'integer',
        'registered_count' => 'integer',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function speaker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'speaker_id');
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_media_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class, 'event_id');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming')
            ->where('start_at', '>', now())
            ->orderBy('start_at');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getTitle(string $locale = 'fr'): ?string
    {
        return $this->title_i18n[$locale] ?? $this->title_i18n['fr'] ?? null;
    }

    public function scopePublicList($query)
    {
        return $query->whereIn('status', ['upcoming', 'ongoing', 'completed'])
            ->orderByDesc('start_at');
    }

    public function isFinished(): bool
    {
        if ($this->status === 'completed') {
            return true;
        }

        if ($this->end_at) {
            return $this->end_at->isPast();
        }

        return $this->start_at?->isPast() ?? false;
    }

    public function isFull(): bool
    {
        return $this->capacity && $this->registered_count >= $this->capacity;
    }

    public function canRegister(): bool
    {
        return ! $this->isFinished()
            && ! $this->isFull()
            && $this->status !== 'cancelled';
    }
}
