<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Support\Concerns\HasTranslations;
use App\Support\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Enums\OrganizationType;

class Organization extends Model
{
    use HasFactory;
    use HasTranslations;
    use HasUlid;

    protected $fillable = [
        'parent_id',
        'type',
        'name',
        'slug',
        'description',
        'logo',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'latitude',
        'longitude',
        'website',
        'is_active',
        'settings',
    ];

    protected array $translatable = ['name', 'description'];

    protected function casts(): array
    {
        return [
            'type' => OrganizationType::class,
            'is_active' => 'boolean',
            'settings' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /**
     * Organisation parente (auto-référence).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'parent_id');
    }

    /**
     * Organisations enfants.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Organization::class, 'parent_id');
    }

    /**
     * Membres de l'organisation.
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * Membres actifs uniquement.
     */
    public function activeMembers(): HasMany
    {
        return $this->memberships()->where('status', 'active');
    }

    /**
     * Scope : organisations d'un certain type.
     */
    public function scopeOfType($query, OrganizationType $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope : organisations racines (sans parent).
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }
}
