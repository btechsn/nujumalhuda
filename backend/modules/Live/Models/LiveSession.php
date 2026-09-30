<?php

namespace Modules\Live\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiveSession extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'stream_id',
        'user_id',
        'session_token',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'country_code',
        'joined_at',
        'left_at',
        'watch_duration_seconds',
        'messages_sent',
        'reactions_sent',
        'metadata',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'watch_duration_seconds' => 'integer',
        'messages_sent' => 'integer',
        'reactions_sent' => 'integer',
        'metadata' => 'array',
    ];

    public function stream(): BelongsTo
    {
        return $this->belongsTo(LiveStream::class, 'stream_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(LiveChatMessage::class, 'session_id');
    }

    public function isActive(): bool
    {
        return is_null($this->left_at);
    }

    public function calculateDuration(): int
    {
        if ($this->left_at) {
            return $this->joined_at->diffInSeconds($this->left_at);
        }

        return $this->joined_at->diffInSeconds(now());
    }
}
