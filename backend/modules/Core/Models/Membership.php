<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Support\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Enums\MembershipStatus;

class Membership extends Model
{
    use HasFactory;
    use HasUlid;

    protected $fillable = [
        'user_id',
        'organization_id',
        'role_id',
        'status',
        'joined_at',
        'expires_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => MembershipStatus::class,
            'joined_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Utilisateur membre.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Organisation.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Rôle dans l'organisation.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Scope : adhésions actives.
     */
    public function scopeActive($query)
    {
        return $query->where('status', MembershipStatus::ACTIVE);
    }

    /**
     * Scope : adhésions expirées.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Vérifie si l'adhésion est active.
     */
    public function isActive(): bool
    {
        return $this->status === MembershipStatus::ACTIVE
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
