<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Support\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Enums\ModerationStatus;

/**
 * Modération polymorphe partagée par tous les contenus modérables.
 *
 * Usage : commentaires, témoignages, questions, sujets/réponses de forum, etc.
 */
class Moderation extends Model
{
    use HasFactory;
    use HasUlid;

    protected $fillable = [
        'moderatable_type',
        'moderatable_id',
        'status',
        'moderated_by',
        'moderated_at',
        'reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => ModerationStatus::class,
            'moderated_at' => 'datetime',
        ];
    }

    /**
     * Contenu modéré (polymorphique).
     */
    public function moderatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Modérateur.
     */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    /**
     * Approuve le contenu.
     */
    public function approve(?User $moderator = null, ?string $notes = null): void
    {
        $this->update([
            'status' => ModerationStatus::APPROVED,
            'moderated_by' => $moderator?->id,
            'moderated_at' => now(),
            'notes' => $notes,
        ]);
    }

    /**
     * Rejette le contenu.
     */
    public function reject(?User $moderator = null, string $reason = '', ?string $notes = null): void
    {
        $this->update([
            'status' => ModerationStatus::REJECTED,
            'moderated_by' => $moderator?->id,
            'moderated_at' => now(),
            'reason' => $reason,
            'notes' => $notes,
        ]);
    }

    /**
     * Signale le contenu.
     */
    public function flag(string $reason = ''): void
    {
        $this->update([
            'status' => ModerationStatus::FLAGGED,
            'reason' => $reason,
        ]);
    }

    /**
     * Scope : contenus en attente.
     */
    public function scopePending($query)
    {
        return $query->where('status', ModerationStatus::PENDING);
    }

    /**
     * Scope : contenus approuvés.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', ModerationStatus::APPROVED);
    }
}
