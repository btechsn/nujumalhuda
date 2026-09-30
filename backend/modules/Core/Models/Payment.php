<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Support\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Enums\PaymentMethod;
use Modules\Core\Enums\PaymentStatus;

/**
 * Registre polymorphe des paiements.
 *
 * Usage : dons, cotisations, frais de scolarité, etc.
 */
class Payment extends Model
{
    use HasFactory;
    use HasUlid;

    protected $fillable = [
        'payable_type',
        'payable_id',
        'user_id',
        'organization_id',
        'method',
        'status',
        'currency',
        'amount_minor',
        'gateway_transaction_id',
        'gateway_response',
        'metadata',
        'completed_at',
        'failed_at',
        'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'method' => PaymentMethod::class,
            'status' => PaymentStatus::class,
            'amount_minor' => 'integer',
            'gateway_response' => 'array',
            'metadata' => 'array',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    /**
     * Objet payé (polymorphique).
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Utilisateur payeur.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Organisation bénéficiaire.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Montant en unité principale (ex: 50000 XOF → 50000).
     */
    public function amount(): float
    {
        // XOF n'a pas de décimales : 1 XOF = 1 unité mineure
        return match ($this->currency) {
            'XOF' => (float) $this->amount_minor,
            default => $this->amount_minor / 100,
        };
    }

    /**
     * Montant formaté.
     */
    public function formattedAmount(): string
    {
        return number_format($this->amount(), 0, ',', ' ') . ' ' . $this->currency;
    }

    /**
     * Marque le paiement comme complété.
     */
    public function markAsCompleted(?string $transactionId = null): void
    {
        $this->update([
            'status' => PaymentStatus::COMPLETED,
            'gateway_transaction_id' => $transactionId ?? $this->gateway_transaction_id,
            'completed_at' => now(),
        ]);
    }

    /**
     * Marque le paiement comme échoué.
     */
    public function markAsFailed(string $reason = ''): void
    {
        $this->update([
            'status' => PaymentStatus::FAILED,
            'failed_at' => now(),
            'metadata' => array_merge($this->metadata ?? [], ['failure_reason' => $reason]),
        ]);
    }

    /**
     * Scope : paiements complétés.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', PaymentStatus::COMPLETED);
    }

    /**
     * Scope : paiements d'une organisation.
     */
    public function scopeForOrganization($query, string $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }
}
