<?php

declare(strict_types=1);

namespace Modules\Announcements\Models;

use App\Support\Concerns\HasTranslations;
use App\Support\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Modules\Announcements\Enums\AnnouncementCategory;
use Modules\Announcements\Enums\AnnouncementPriority;

class Announcement extends Model
{
    use HasFactory;
    use HasTranslations;
    use HasUlid;

    protected $fillable = [
        'title',
        'message',
        'category',
        'priority',
        'starts_at',
        'ends_at',
        'is_active',
        'action_url',
        'metadata',
    ];

    protected array $translatable = ['title', 'message'];

    protected function casts(): array
    {
        return [
            'category' => AnnouncementCategory::class,
            'priority' => AnnouncementPriority::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /**
     * Audiences ciblées.
     */
    public function audiences(): HasMany
    {
        return $this->hasMany(AnnouncementAudience::class);
    }

    /**
     * Lectures enregistrées.
     */
    public function reads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    /**
     * Scope : annonces actives.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    /**
     * Scope : annonces visibles par un utilisateur.
     */
    public function scopeVisibleBy($query, $user = null)
    {
        if (!$user) {
            return $query->whereHas('audiences', fn ($audiences) => $audiences->where('type', 'public'));
        }

        $memberships = $user->memberships()->where('status', 'active')->get(['role_id', 'organization_id']);
        $roleIds = $memberships->pluck('role_id')->filter()->all();
        $organizationIds = $memberships->pluck('organization_id')->filter()->all();
        $promotionIds = DB::table('enrollments')
            ->where('user_id', $user->id)
            ->whereIn('status', ['approved', 'active', 'completed'])
            ->pluck('promotion_id')
            ->all();

        return $query->where(function ($outer) use ($roleIds, $organizationIds, $promotionIds) {
            $outer->whereHas('audiences', fn ($audiences) => $audiences->where('type', 'public'))
                ->orWhereHas('audiences', function ($audiences) use ($roleIds, $organizationIds, $promotionIds) {
                    $audiences->where('type', 'members');

                    if ($roleIds !== []) {
                        $audiences->orWhere(fn ($query) => $query->where('type', 'role')->whereIn('target_id', $roleIds));
                    }
                    if ($organizationIds !== []) {
                        $audiences->orWhere(fn ($query) => $query->where('type', 'organization')->whereIn('target_id', $organizationIds));
                    }
                    if ($promotionIds !== []) {
                        $audiences->orWhere(fn ($query) => $query->where('type', 'cohort')->whereIn('target_id', $promotionIds));
                    }
                });
        });
    }

    /**
     * Vérifie si l'annonce est visible actuellement.
     */
    public function isVisible(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }
}
