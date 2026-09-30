<?php

namespace Modules\Education\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Models\User;

class EnrollmentDocument extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'enrollment_id',
        'document_type',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'original_name',
        'verification_status',
        'verified_by',
        'verified_at',
        'verification_notes',
        'metadata',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'verified_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Relations
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('verification_status', 'pending');
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopeRejected($query)
    {
        return $query->where('verification_status', 'rejected');
    }

    /**
     * Helpers
     */
    public function verify(?string $notes = null): void
    {
        $this->update([
            'verification_status' => 'verified',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'verification_notes' => $notes,
        ]);
    }

    public function reject(string $reason): void
    {
        $this->update([
            'verification_status' => 'rejected',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'verification_notes' => $reason,
        ]);
    }

    public function isPending(): bool
    {
        return $this->verification_status === 'pending';
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function isRejected(): bool
    {
        return $this->verification_status === 'rejected';
    }

    /**
     * Obtenir l'URL publique du document
     */
    public function getUrl(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Obtenir l'URL de téléchargement
     */
    public function getDownloadUrl(): string
    {
        return route('education.documents.download', $this->id);
    }

    /**
     * Obtenir la taille formatée
     */
    public function getFormattedSize(): string
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Obtenir le label du type de document
     */
    public function getTypeLabel(): string
    {
        return match ($this->document_type) {
            'id_card' => 'Carte d\'identité',
            'birth_certificate' => 'Acte de naissance',
            'photo' => 'Photo d\'identité',
            'diploma' => 'Diplôme',
            'cv' => 'CV',
            'recommendation' => 'Lettre de recommandation',
            'other' => 'Autre',
            default => $this->document_type,
        };
    }

    /**
     * Boot
     */
    protected static function boot()
    {
        parent::boot();

        // Supprimer le fichier physique lors de la suppression
        static::deleting(function ($document) {
            if ($document->file_path && Storage::disk('private')->exists($document->file_path)) {
                Storage::disk('private')->delete($document->file_path);
            }
        });
    }
}
