<?php

namespace Modules\News\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\User;
use Modules\Core\Services\ModerationService;
use Modules\News\Events\CommentApproved;
use Modules\News\Events\CommentRejected;

class ArticleComment extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'article_id',
        'user_id',
        'parent_id',
        'content',
        'status',
        'moderated_at',
        'moderated_by',
        'moderation_reason',
        'reports_count',
        'spam_score',
        'spam_flags',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'moderated_at' => 'datetime',
        'reports_count' => 'integer',
        'spam_score' => 'integer',
        'spam_flags' => 'array',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'article_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ArticleComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ArticleComment::class, 'parent_id');
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeSpam($query)
    {
        return $query->where('status', 'spam');
    }

    public function approve(User $moderator): void
    {
        $alreadyApproved = $this->status === 'approved';

        $this->update([
            'status' => 'approved',
            'moderated_at' => now(),
            'moderated_by' => $moderator->id,
        ]);

        $this->article->updateCommentsCount();

        if (! $alreadyApproved) {
            event(new CommentApproved($this));
        }

        app(ModerationService::class)->sync($this, 'approved', $moderator);
    }

    public function reject(User $moderator, string $reason): void
    {
        $alreadyRejected = $this->status === 'rejected';

        $this->update([
            'status' => 'rejected',
            'moderated_at' => now(),
            'moderated_by' => $moderator->id,
            'moderation_reason' => $reason,
        ]);

        if (! $alreadyRejected) {
            event(new CommentRejected($this));
        }

        app(ModerationService::class)->sync($this, 'rejected', $moderator, $reason);
    }
}
