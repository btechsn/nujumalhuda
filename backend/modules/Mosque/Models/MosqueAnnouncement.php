<?php

namespace Modules\Mosque\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\Organization;

class MosqueAnnouncement extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'title_i18n',
        'content_i18n',
        'type',
        'display_from',
        'display_to',
        'priority',
        'is_active',
        'is_pinned',
    ];

    protected $casts = [
        'title_i18n' => 'array',
        'content_i18n' => 'array',
        'display_from' => 'datetime',
        'display_to' => 'datetime',
        'is_active' => 'boolean',
        'is_pinned' => 'boolean',
        'priority' => 'integer',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('display_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('display_to')
                    ->orWhere('display_to', '>=', now());
            });
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('is_pinned', 'desc')
            ->orderBy('priority', 'desc')
            ->orderBy('display_from', 'desc');
    }

    public function getTitle(string $locale = 'fr'): ?string
    {
        return $this->title_i18n[$locale] ?? $this->title_i18n['fr'] ?? null;
    }

    public function getContent(string $locale = 'fr'): ?string
    {
        return $this->content_i18n[$locale] ?? $this->content_i18n['fr'] ?? null;
    }
}
