<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Support\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Drapeaux de consentement parental pour un élève mineur, distincts par
 * usage (cahier des charges, §8.3, point 1).
 */
class MinorConsent extends Model
{
    use HasUlid;

    protected $fillable = [
        'user_id',
        'gallery_image_consent',
        'recitation_audio_consent',
        'public_name_consent',
        'consented_by',
        'consented_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'gallery_image_consent' => 'boolean',
            'recitation_audio_consent' => 'boolean',
            'public_name_consent' => 'boolean',
            'consented_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function consentedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consented_by');
    }
}
