<?php

namespace Modules\Mosque\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\Media;
use Modules\Core\Models\Organization;
use Modules\Core\Models\User;

class Khutba extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'title_i18n',
        'summary_i18n',
        'speaker_id',
        'speaker_name',
        'date',
        'time',
        'content_i18n',
        'key_points',
        'audio_media_id',
        'video_media_id',
        'youtube_url',
        'references',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'title_i18n' => 'array',
        'summary_i18n' => 'array',
        'content_i18n' => 'array',
        'references' => 'array',
        'date' => 'date',
        'time' => 'datetime:H:i',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function speaker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'speaker_id');
    }

    public function audio(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'audio_media_id');
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'video_media_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('date', 'desc')->limit($limit);
    }

    public function getTitle(string $locale = 'fr'): ?string
    {
        return $this->title_i18n[$locale] ?? $this->title_i18n['fr'] ?? null;
    }
}
