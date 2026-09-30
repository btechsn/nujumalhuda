<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Support\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Relation parent/tuteur ↔ élève.
 *
 * Un élève peut avoir plusieurs tuteurs, et un tuteur plusieurs élèves :
 * relation many-to-many matérialisée.
 */
class Guardianship extends Model
{
    use HasFactory;
    use HasUlid;

    protected $fillable = [
        'guardian_id',
        'ward_id',
        'relationship',
        'is_primary',
        'can_view_progress',
        'can_receive_notifications',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'can_view_progress' => 'boolean',
            'can_receive_notifications' => 'boolean',
        ];
    }

    /**
     * Tuteur (parent).
     */
    public function guardian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guardian_id');
    }

    /**
     * Élève (enfant sous tutelle).
     */
    public function ward(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ward_id');
    }

    /**
     * Scope : tutelles primaires (contact principal).
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}
