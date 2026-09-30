<?php

namespace Modules\Education\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\Organization;
use Modules\Core\Models\User;

class Promotion extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'program_id',
        'organization_id',
        'name_i18n',
        'code',
        'academic_year',
        'start_date',
        'end_date',
        'capacity',
        'min_students',
        'main_teacher_id',
        'schedule',
        'location',
        'status',
        'is_open_for_enrollment',
        'enrolled_count',
        'active_count',
    ];

    protected $casts = [
        'name_i18n' => 'array',
        'schedule' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_open_for_enrollment' => 'boolean',
        'capacity' => 'integer',
        'min_students' => 'integer',
        'academic_year' => 'integer',
        'enrolled_count' => 'integer',
        'active_count' => 'integer',
    ];

    /**
     * Relations
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function mainTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'main_teacher_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    /**
     * Scopes
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming');
    }

    public function scopeOngoing($query)
    {
        return $query->where('status', 'ongoing');
    }

    public function scopeOpenForEnrollment($query)
    {
        return $query->where('is_open_for_enrollment', true);
    }

    public function scopeByYear($query, int $year)
    {
        return $query->where('academic_year', $year);
    }

    /**
     * Helpers
     */
    public function getName(string $locale = 'fr'): ?string
    {
        return $this->name_i18n[$locale] ?? $this->name_i18n['fr'] ?? null;
    }

    public function isFull(): bool
    {
        return $this->capacity && $this->enrolled_count >= $this->capacity;
    }

    public function hasMinimumStudents(): bool
    {
        return !$this->min_students || $this->enrolled_count >= $this->min_students;
    }

    public function canEnroll(): bool
    {
        return $this->is_open_for_enrollment && !$this->isFull();
    }

    public function updateEnrollmentCount(): void
    {
        $this->enrolled_count = $this->enrollments()
            ->whereIn('status', ['approved', 'active', 'completed'])
            ->count();

        $this->active_count = $this->enrollments()
            ->where('status', 'active')
            ->count();

        $this->saveQuietly();
    }
}
