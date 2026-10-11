<?php

namespace Modules\Live\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Support\Facades\Storage;
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
        'external_url',
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

    public function markEnded(?\DateTimeInterface $endedAt = null): void
    {
        if (! $this->isLive()) {
            return;
        }

        $endedAt = $endedAt ? \Illuminate\Support\Carbon::parse($endedAt) : now();

        $this->forceFill([
            'status' => 'ended',
            'ended_at' => $endedAt,
            'duration_seconds' => $this->started_at
                ? (int) abs($this->started_at->diffInSeconds($endedAt))
                : $this->duration_seconds,
        ])->save();
    }

    /**
     * @param  array{started_at: ?\Illuminate\Support\Carbon, ended_at: ?\Illuminate\Support\Carbon, duration_seconds: ?int, views: ?int}  $broadcast
     */
    public function recordYoutubeBroadcast(array $broadcast, bool $close): void
    {
        $payload = [];

        if ($close && $this->isLive()) {
            $payload['status'] = 'ended';
        }

        if (! $this->started_at && $broadcast['started_at']) {
            $payload['started_at'] = $broadcast['started_at'];
        }

        if ($broadcast['ended_at'] && (! $this->ended_at || $close)) {
            $payload['ended_at'] = $broadcast['ended_at'];
        }

        if ($broadcast['duration_seconds']) {
            $payload['duration_seconds'] = $broadcast['duration_seconds'];
        }

        if ($broadcast['views'] !== null) {
            $payload['total_views'] = max((int) $this->total_views, (int) $broadcast['views']);
        }

        if ($payload !== []) {
            $this->forceFill($payload)->save();
        }
    }

    public function getLocalizedTitle(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->title[$locale] ?? $this->title['fr'] ?? '';
    }

    public function getLocalizedDescription(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $html = $this->description[$locale] ?? $this->description['fr'] ?? null;

        if (! is_string($html) || trim($html) === '') {
            return null;
        }

        $text = trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return $text === '' ? null : $text;
    }

    public function publicThumbnailUrl(): ?string
    {
        $path = trim((string) $this->thumbnail_url);

        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    public function getStreamUrl(): string
    {
        $base = $this->channel?->whep_url;

        return filled($base) ? $base.'/'.$this->publish_key : '';
    }

    public function getHlsUrl(): string
    {
        $base = $this->channel?->hls_url;

        return filled($base) ? $base.'/'.$this->publish_key.'/index.m3u8' : '';
    }

    public function getRtmpIngestUrl(): string
    {
        $base = $this->channel?->rtmp_ingest_url;

        return filled($base) ? $base.'/'.$this->publish_key : '';
    }

    /**
     * Adresse lisible dans un iframe quand la séance passe par YouTube ou Facebook.
     */
    public function getEmbedUrl(): ?string
    {
        $url = trim((string) $this->external_url);

        if ($url === '') {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = (string) parse_url($url, PHP_URL_PATH);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        if (str_contains($host, 'youtu.be')) {
            $id = trim($path, '/');

            return $id === '' ? null : "https://www.youtube.com/embed/{$id}?autoplay=1";
        }

        if (str_contains($host, 'youtube.com')) {
            if (str_contains($path, '/embed/')) {
                return $url;
            }

            if (filled($query['v'] ?? null)) {
                return 'https://www.youtube.com/embed/'.$query['v'].'?autoplay=1';
            }

            if (str_starts_with($path, '/live/')) {
                return 'https://www.youtube.com/embed/'.trim(substr($path, 6), '/').'?autoplay=1';
            }

            return null;
        }

        if (str_contains($host, 'facebook.com') || str_contains($host, 'fb.watch')) {
            return 'https://www.facebook.com/plugins/video.php?href='.urlencode($url).'&autoplay=1';
        }

        return null;
    }
}
