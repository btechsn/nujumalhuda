<?php

namespace Modules\Mosque\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Core\Models\Organization;
use Modules\Core\Models\User;

class EventRegistration extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $table = 'mosque_event_registrations';

    protected $fillable = [
        'organization_id',
        'event_id',
        'user_id',
        'participant_name',
        'participant_email',
        'participant_phone',
        'number_of_attendees',
        'message',
        'status',
        'registered_at',
        'confirmed_at',
        'cancelled_at',
        'confirmation_code',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
        'number_of_attendees' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($registration) {
            if (empty($registration->confirmation_code)) {
                $registration->confirmation_code = Str::upper(Str::random(8));
            }
        });
    }

    /**
     * Organisation
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Événement
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(MosqueEvent::class, 'event_id');
    }

    /**
     * Utilisateur (si connecté)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeAttended($query)
    {
        return $query->where('status', 'attended');
    }

    /**
     * Helpers
     */
    public function confirm(): void
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    public function cancel(): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    public function markAsAttended(): void
    {
        $this->update([
            'status' => 'attended',
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function hasAttended(): bool
    {
        return $this->status === 'attended';
    }
}
