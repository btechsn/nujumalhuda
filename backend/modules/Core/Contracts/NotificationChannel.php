<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

use Modules\Core\Data\NotificationData;
use Modules\Core\Models\User;

/**
 * Interface pour les canaux de notification personnalisés.
 */
interface NotificationChannel
{
    /**
     * Envoie une notification via ce canal.
     */
    public function send(User $user, NotificationData $notification): bool;

    /**
     * Nom du canal.
     */
    public function getName(): string;

    /**
     * Vérifie si le canal est disponible pour cet utilisateur.
     */
    public function isAvailableFor(User $user): bool;
}
