<?php

namespace Modules\Live\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StreamAnalytic extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'stream_id',
        'recorded_at',
        'concurrent_viewers',
        'new_viewers',
        'returning_viewers',
        'chat_messages',
        'reactions',
        'bitrate_kbps',
        'frame_rate',
        'buffer_ratio',
        'viewer_locations',
        'device_breakdown',
        'metadata',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'concurrent_viewers' => 'integer',
        'new_viewers' => 'integer',
        'returning_viewers' => 'integer',
        'chat_messages' => 'integer',
        'reactions' => 'integer',
        'bitrate_kbps' => 'integer',
        'frame_rate' => 'integer',
        'buffer_ratio' => 'decimal:2',
        'viewer_locations' => 'array',
        'device_breakdown' => 'array',
        'metadata' => 'array',
    ];

    public function stream(): BelongsTo
    {
        return $this->belongsTo(LiveStream::class, 'stream_id');
    }
}
