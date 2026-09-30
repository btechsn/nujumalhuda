<?php

namespace Modules\Academics\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HifzMilestone extends Model
{
    use HasUlids;

    protected $fillable = [
        'slug', 'title_i18n', 'description_i18n', 'from_juz', 'to_juz', 'display_order',
    ];

    protected $casts = [
        'title_i18n' => 'array',
        'description_i18n' => 'array',
        'from_juz' => 'integer',
        'to_juz' => 'integer',
        'display_order' => 'integer',
    ];

    public function badge(): HasOne
    {
        return $this->hasOne(Badge::class, 'milestone_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(StudentProgress::class, 'milestone_id');
    }
}
