<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Support\Concerns\HasUlid;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements HasAvatar, HasName
{
    use HasApiTokens;
    use HasFactory;
    use HasUlid;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'locale',
        'timezone',
        'avatar',
        'email_verified_at',
        'phone_verified_at',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Nom complet de l'utilisateur.
     */
    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getNameAttribute(): string
    {
        return $this->fullName();
    }

    public function getFilamentName(): string
    {
        return $this->fullName();
    }

    public function getFilamentAvatarUrl(): ?string
    {
        $path = $this->avatar;

        if (! filled($path) && class_exists(\Modules\Education\Models\Teacher::class)) {
            $teacher = \Modules\Education\Models\Teacher::query()->find($this->id);
            $availability = $teacher?->availability;
            $path = is_array($availability) ? ($availability['photo'] ?? null) : null;
        }

        if (! filled($path)) {
            return $this->initialsAvatar();
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
            return $path;
        }

        if (! str_starts_with($path, '/') && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return asset(ltrim((string) $path, '/'));
    }

    private function initialsAvatar(): string
    {
        $initials = collect(preg_split('/\s+/u', $this->fullName()) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64">'
            .'<rect width="64" height="64" rx="32" fill="#0b5a31"/>'
            .'<text x="32" y="38" text-anchor="middle" fill="#ffffff" font-family="sans-serif" font-size="22" font-weight="700">'
            .e($initials)
            .'</text></svg>';

        return 'data:image/svg+xml,'.rawurlencode($svg);
    }

    /**
     * Adhésions à des organisations.
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * Tutelles (enfants dont l'utilisateur est tuteur).
     */
    public function wards(): HasMany
    {
        return $this->hasMany(Guardianship::class, 'guardian_id');
    }

    /**
     * Tuteurs (parents/tuteurs de l'utilisateur).
     */
    public function guardians(): HasMany
    {
        return $this->hasMany(Guardianship::class, 'ward_id');
    }

    /**
     * Préférences de notification.
     */
    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    /**
     * Abonnements push.
     */
    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    /**
     * Médias uploadés.
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class, 'uploaded_by');
    }

    /**
     * Vérifie si l'utilisateur a un rôle dans une organisation.
     */
    public function hasRole(string $role, string $organizationId): bool
    {
        return $this->memberships()
            ->where('organization_id', $organizationId)
            ->where('status', 'active')
            ->whereHas('role', fn($q) => $q->where('name', $role))
            ->exists();
    }

    /**
     * Vérifie si l'utilisateur a une permission dans une organisation.
     */
    public function hasPermission(string $permission, string $organizationId): bool
    {
        return $this->memberships()
            ->where('organization_id', $organizationId)
            ->where('status', 'active')
            ->whereHas('role.permissions', fn($q) => $q->where('name', $permission))
            ->exists();
    }

    public function isStaff(): bool
    {
        return $this->memberships()
            ->where('status', 'active')
            ->whereHas('role', fn ($query) => $query->whereIn('name', [
                'admin',
                'director',
                'secretary',
                'teacher',
                'academic_admin',
            ]))
            ->exists();
    }
}
