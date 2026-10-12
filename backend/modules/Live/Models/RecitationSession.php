<?php

namespace Modules\Live\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\User;

class RecitationSession extends Model
{
    use HasUlids;

    protected $fillable = [
        'trigger', 'live_stream_id', 'student_id', 'teacher_id',
        'milestone_id', 'title', 'starts_at', 'status', 'visibility',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Cahier des charges, §8.3, point 2 : par défaut restreinte aux
     * enseignants et tuteurs de l'élève. Public seulement si le champ vaut
     * explicitement « public » — et un élève sans consentement ne peut
     * jamais atteindre cet état (cf. Filament\Resources\RecitationSessionResource).
     */
    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }
}
