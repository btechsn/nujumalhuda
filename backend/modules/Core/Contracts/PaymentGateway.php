<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

use Modules\Core\Data\PaymentData;

/**
 * Interface pour les passerelles de paiement.
 *
 * Implémentations prévues :
 * - WavePaymentGateway (Phase 6)
 * - OrangeMoneyPaymentGateway (Phase 6)
 */
interface PaymentGateway
{
    /**
     * Initie un paiement et retourne l'URL de redirection ou le code de confirmation.
     */
    public function initiate(PaymentData $payment): array;

    /**
     * Vérifie le statut d'un paiement.
     */
    public function verify(string $transactionId): array;

    /**
     * Annule un paiement en attente.
     */
    public function cancel(string $transactionId): bool;

    /**
     * Rembourse un paiement complété.
     */
    public function refund(string $transactionId, ?int $amount = null): bool;

    /**
     * Nom de la passerelle.
     */
    public function getName(): string;
}
