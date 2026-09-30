<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Support\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Enums\NotificationChannel;

class NotificationPreference extends Model
{
    use HasFactory;
    use HasUlid;

    protected $fillable = [
        'user_id',
        'category',
        'channel',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'channel' => NotificationChannel::class,
            'enabled' => 'boolean',
        ];
    }

    /**
     * Utilisateur.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope : préférences activées.
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Scope : préférences d'un canal spécifique.
     */
    public function scopeOfChannel($query, NotificationChannel $channel)
    {
        return $query->where('channel', $channel);
    }
}
