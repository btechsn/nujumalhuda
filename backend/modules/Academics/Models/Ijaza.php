<?php

namespace Modules\Academics\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Modules\Academics\Services\IjazaDocument;
use Modules\Core\Models\User;

class Ijaza extends Model
{
    use HasUlids;

    protected $fillable = [
        'teacher_id', 'student_id', 'scope_i18n', 'sanad_i18n', 'signed_at', 'is_public',
        'verification_code', 'document_path',
    ];

    protected $casts = [
        'scope_i18n' => 'array',
        'sanad_i18n' => 'array',
        'signed_at' => 'datetime',
        'is_public' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Ijaza $ijaza) {
            $sanad = $ijaza->sanad_i18n['fr'] ?? '';
            if (blank($ijaza->teacher_id) || blank($sanad) || blank($ijaza->signed_at)) {
                throw new InvalidArgumentException(
                    'Une ijaza exige un enseignant nommé, la chaîne de transmission et une date de signature. Elle n\'est jamais générée automatiquement.'
                );
            }
            if (blank($ijaza->verification_code)) {
                $ijaza->verification_code = strtoupper(Str::random(10));
            }
        });

        static::created(function (Ijaza $ijaza): void {
            app(IjazaDocument::class)->write($ijaza);
        });
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
