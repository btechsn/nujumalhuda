<?php

namespace Modules\Resources\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AudioRecitation extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'surah_number', 'surah_name_i18n', 'reciter', 'audio_url',
        'duration_seconds', 'is_public', 'play_count',
    ];

    protected $casts = [
        'surah_name_i18n' => 'array',
        'surah_number' => 'integer',
        'duration_seconds' => 'integer',
        'is_public' => 'boolean',
        'play_count' => 'integer',
    ];
}
