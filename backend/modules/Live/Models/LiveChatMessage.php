<?php

namespace Modules\Live\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiveChatMessage extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'stream_id',
        'session_id',
        'user_id',
        'message',
        'type',
        'status',
        'moderated_by',
        'moderated_at',
        'moderation_reason',
        'spam_score',
        'spam_flags',
        'ip_address',
        'metadata',
    ];

    protected $casts = [
        'moderated_at' => 'datetime',
        'spam_score' => 'integer',
        'spam_flags' => 'array',
        'metadata' => 'array',
    ];

    public function stream(): BelongsTo
    {
        return $this->belongsTo(LiveStream::class, 'stream_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class, 'session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function isVisible(): bool
    {
        return $this->status === 'visible';
    }

    public function isFlagged(): bool
    {
        return $this->status === 'flagged';
    }

    public function isSpam(): bool
    {
        return $this->spam_score >= 70;
    }

    public function hide(User $moderator, string $reason): void
    {
        $this->update([
            'status' => 'hidden',
            'moderated_by' => $moderator->id,
            'moderated_at' => now(),
            'moderation_reason' => $reason,
        ]);
    }

    public function flag(string $reason): void
    {
        $this->update([
            'status' => 'flagged',
            'moderation_reason' => $reason,
        ]);
    }
}
