<?php

namespace Modules\Live\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class VodRecording extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'stream_id',
        'slug',
        'title',
        'description',
        'storage_path',
        'hls_url',
        'mp4_url',
        'duration_seconds',
        'file_size_bytes',
        'resolution',
        'bitrate',
        'status',
        'views_count',
        'is_public',
        'is_downloadable',
        'thumbnail_url',
        'chapters',
        'published_at',
        'metadata',
    ];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'duration_seconds' => 'integer',
        'file_size_bytes' => 'integer',
        'bitrate' => 'integer',
        'views_count' => 'integer',
        'is_public' => 'boolean',
        'is_downloadable' => 'boolean',
        'chapters' => 'array',
        'published_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vod) {
            if (empty($vod->slug)) {
                $vod->slug = Str::slug($vod->getLocalizedTitle()) . '-' . Str::random(8);
            }
        });
    }

    public function stream(): BelongsTo
    {
        return $this->belongsTo(LiveStream::class, 'stream_id');
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
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

    public function getFormattedDuration(): string
    {
        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function getFormattedFileSize(): string
    {
        if (!$this->file_size_bytes) {
            return 'N/A';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size_bytes;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }
}
