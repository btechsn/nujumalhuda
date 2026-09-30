<?php

namespace Modules\Community\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class GalleryItem extends Model
{
    use HasUlids;

    protected $fillable = [
        'kind',
        'caption_i18n',
        'event_name',
        'media_url',
        'thumbnail_url',
        'taken_on',
        'is_public',
    ];

    protected $casts = [
        'caption_i18n' => 'array',
        'taken_on' => 'date',
        'is_public' => 'boolean',
    ];
}
