<?php

namespace Modules\Community\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'description_i18n',
        'logo_url',
        'website_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'description_i18n' => 'array',
        'is_active' => 'boolean',
    ];
}
