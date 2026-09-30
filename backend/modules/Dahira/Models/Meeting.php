<?php

namespace Modules\Dahira\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    use HasUlids;

    protected $fillable = [
        'dahira_group_id', 'title', 'starts_at', 'location', 'agenda', 'convened_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'convened_at' => 'datetime',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(DahiraGroup::class, 'dahira_group_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(MeetingAttendance::class);
    }
}
