<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Support\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Enums\NotificationChannel as NotificationChannelEnum;

class Notification extends Model
{
    use HasFactory;
    use HasUlid;

    protected $fillable = [
        'user_id',
        'type',
        'channel',
        'title',
        'message',
        'data',
        'action_url',
        'read_at',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'channel' => NotificationChannelEnum::class,
            'data' => 'array',
            'read_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    /**
     * Utilisateur destinataire.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Marque la notification comme lue.
     */
    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Scope : notifications non lues.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope : notifications lues.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope : notifications d'un certain type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
