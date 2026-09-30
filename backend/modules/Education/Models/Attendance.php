<?php

namespace Modules\Education\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\User;

class Attendance extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'enrollment_id',
        'session_id',
        'status',
        'marked_at',
        'marked_by',
        'excuse',
        'is_excused',
        'notes',
        'participation_score',
    ];

    protected $casts = [
        'marked_at' => 'datetime',
        'is_excused' => 'boolean',
        'participation_score' => 'integer',
    ];

    /**
     * Relations
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    public function marker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    /**
     * Scopes
     */
    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    public function scopeExcused($query)
    {
        return $query->where('is_excused', true);
    }

    /**
     * Helpers
     */
    public function isPresent(): bool
    {
        return in_array($this->status, ['present', 'late']);
    }

    public function isAbsent(): bool
    {
        return $this->status === 'absent';
    }
}
