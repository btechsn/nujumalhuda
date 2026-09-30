<?php

namespace Modules\Academics\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Badge extends Model
{
    use HasUlids;

    protected $fillable = ['milestone_id', 'name_i18n', 'description_i18n', 'icon'];

    protected $casts = [
        'name_i18n' => 'array',
        'description_i18n' => 'array',
    ];

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(HifzMilestone::class, 'milestone_id');
    }
}
