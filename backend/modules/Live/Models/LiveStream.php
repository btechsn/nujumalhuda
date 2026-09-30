<?php

namespace Modules\Live\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiveStream extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'channel_id',
        'title',
        'description',
        'type',
        'status',
        'publish_key',
        'scheduled_at',
        'started_at',
        'ended_at',
        'duration_seconds',
        'peak_viewers',
        'total_views',
        'chat_messages_count',
        'enable_chat',
        'enable_reactions',
        'is_featured',
        'thumbnail_url',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_seconds' => 'integer',
        'peak_viewers' => 'integer',
        'total_views' => 'integer',
        'chat_messages_count' => 'integer',
        'enable_chat' => 'boolean',
        'enable_reactions' => 'boolean',
        'is_featured' => 'boolean',
        'metadata' => 'array',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(LiveChannel::class, 'channel_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(LiveSession::class, 'stream_id');
    }

    public function activeSessions(): HasMany
    {
        return $this->sessions()->whereNull('left_at');
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(LiveChatMessage::class, 'stream_id');
    }

    public function visibleChatMessages(): HasMany
    {
        return $this->chatMessages()->where('status', 'visible');
    }

    public function recording(): HasOne
    {
        return $this->hasOne(VodRecording::class, 'stream_id');
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(StreamAnalytic::class, 'stream_id');
    }

    public function getCurrentViewerCount(): int
    {
        return $this->activeSessions()->count();
    }

    public function isLive(): bool
    {
        return $this->status === 'live';
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isEnded(): bool
    {
        return in_array($this->status, ['ended', 'archived']);
    }

    public function getLocalizedTitle(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->title[$locale] ?? $this->title['fr'] ?? '';
    }

    public function getLocalizedDescription(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->description[$locale] ?? $this->description['fr'] ?? null;
    }

    public function getStreamUrl(): string
    {
        return $this->channel->whep_url . '/' . $this->publish_key;
    }

    public function getHlsUrl(): string
    {
        return $this->channel->hls_url . '/' . $this->publish_key . '/index.m3u8';
    }

    public function getRtmpIngestUrl(): string
    {
        return $this->channel->rtmp_ingest_url . '/' . $this->publish_key;
    }
}
