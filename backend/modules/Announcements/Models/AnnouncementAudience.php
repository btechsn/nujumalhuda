<?php

declare(strict_types=1);

namespace Modules\Announcements\Models;

use App\Support\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Announcements\Enums\AudienceType;

class AnnouncementAudience extends Model
{
    use HasFactory;
    use HasUlid;

    protected $fillable = [
        'announcement_id',
        'type',
        'target_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => AudienceType::class,
        ];
    }

    /**
     * Annonce parente.
     */
    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }
}
