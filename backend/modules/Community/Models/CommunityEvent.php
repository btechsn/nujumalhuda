<?php

namespace Modules\Community\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunityEvent extends Model
{
    use HasUlids, SoftDeletes;

    protected $table = 'events';

    protected $fillable = [
        'title_i18n', 'description_i18n', 'starts_at', 'ends_at',
        'location', 'capacity', 'is_published',
    ];

    protected $casts = [
        'title_i18n' => 'array',
        'description_i18n' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_published' => 'boolean',
        'capacity' => 'integer',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class, 'event_id');
    }
}
