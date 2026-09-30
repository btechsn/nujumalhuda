<?php

namespace Modules\Resources\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class DailyContent extends Model
{
    use HasUlids;

    protected $fillable = [
        'display_date', 'type', 'arabic_text', 'translation_i18n',
        'commentary_i18n', 'source', 'reference', 'is_published',
    ];

    protected $casts = [
        'display_date' => 'date',
        'translation_i18n' => 'array',
        'commentary_i18n' => 'array',
        'is_published' => 'boolean',
    ];
}
