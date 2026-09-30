<?php

namespace Modules\Education\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\Payment;
use Modules\Core\Models\User;

class Enrollment extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'promotion_id',
        'status',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
        'application_data',
        'documents',
        'motivation',
        'previous_education',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'fees_paid',
        'payment_id',
        'attendance_rate',
        'start_date',
        'completion_date',
        'completion_notes',
    ];

    protected $casts = [
        'application_data' => 'array',
        'documents' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'fees_paid' => 'boolean',
        'attendance_rate' => 'integer',
        'start_date' => 'date',
        'completion_date' => 'date',
    ];

    /**
     * Relations
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Helpers
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function approve(User $reviewer, ?string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer->id,
            'review_notes' => $notes,
        ]);
    }

    public function reject(User $reviewer, string $notes): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer->id,
            'review_notes' => $notes,
        ]);
    }

    public function activate(): void
    {
        $this->update([
            'status' => 'active',
            'start_date' => now(),
        ]);
    }

    public function complete(?string $notes = null): void
    {
        $this->update([
            'status' => 'completed',
            'completion_date' => now(),
            'completion_notes' => $notes,
        ]);
    }

    public function updateAttendanceRate(): void
    {
        $totalSessions = $this->attendances()->count();

        if ($totalSessions === 0) {
            $this->attendance_rate = 0;
            $this->saveQuietly();
            return;
        }

        $presentCount = $this->attendances()
            ->whereIn('status', ['present', 'late'])
            ->count();

        $this->attendance_rate = (int) round(($presentCount / $totalSessions) * 100);
        $this->saveQuietly();
    }

    public function getStudentNameAttribute(): string
    {
        $fromApplication = $this->application_data['student_name'] ?? null;
        if (is_string($fromApplication) && $fromApplication !== '') {
            return $fromApplication;
        }

        return $this->user?->fullName() ?: 'Élève';
    }

    public function getStudentEmailAttribute(): ?string
    {
        $fromApplication = $this->application_data['student_email'] ?? null;

        return is_string($fromApplication) && $fromApplication !== ''
            ? $fromApplication
            : $this->user?->email;
    }
}
