<?php

namespace Modules\Community\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class SmsDigestSubscriber extends Model
{
    use HasUlids;

    protected $fillable = ['phone', 'locale', 'consented_at', 'is_active', 'last_prepared_at', 'last_sent_at'];

    protected $casts = [
        'consented_at' => 'datetime',
        'is_active' => 'boolean',
        'last_prepared_at' => 'datetime',
        'last_sent_at' => 'datetime',
    ];
}
