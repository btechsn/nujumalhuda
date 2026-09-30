<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Support\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;
    use HasUlid;

    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'uploaded_by',
        'collection',
        'name',
        'file_name',
        'mime_type',
        'disk',
        'path',
        'size',
        'width',
        'height',
        'duration',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'duration' => 'integer',
            'metadata' => 'array',
        ];
    }

    /**
     * Modèle propriétaire du média (polymorphique).
     */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Utilisateur ayant uploadé le média.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * URL publique du média.
     */
    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * URL temporaire (pour disques privés).
     */
    public function temporaryUrl(\DateTimeInterface $expiration): string
    {
        return Storage::disk($this->disk)->temporaryUrl($this->path, $expiration);
    }

    /**
     * Vérifie si le média est une image.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Vérifie si le média est une vidéo.
     */
    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    /**
     * Vérifie si le média est un audio.
     */
    public function isAudio(): bool
    {
        return str_starts_with($this->mime_type, 'audio/');
    }

    /**
     * Supprime physiquement le fichier.
     */
    public function deleteFile(): bool
    {
        return Storage::disk($this->disk)->delete($this->path);
    }
}
