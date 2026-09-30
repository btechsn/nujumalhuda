<?php

namespace Modules\Live\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiveChannel extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'type',
        'rtmp_ingest_url',
        'whep_url',
        'hls_url',
        'max_bitrate',
        'priority',
        'is_active',
        'requires_moderation',
        'metadata',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'max_bitrate' => 'integer',
        'priority' => 'integer',
        'is_active' => 'boolean',
        'requires_moderation' => 'boolean',
        'metadata' => 'array',
    ];

    public function streams(): HasMany
    {
        return $this->hasMany(LiveStream::class, 'channel_id');
    }

    public function activeStreams(): HasMany
    {
        return $this->streams()->where('status', 'live');
    }

    public function getLocalizedName(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->name[$locale] ?? $this->name['fr'] ?? '';
    }

    public function getLocalizedDescription(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->description[$locale] ?? $this->description['fr'] ?? null;
    }
}
