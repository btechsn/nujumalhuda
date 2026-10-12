<?php

declare(strict_types=1);

namespace Modules\Core\Support;

/**
 * Cahier des charges §8.3 point 5 : durée de conservation par catégorie.
 *
 * Les délais ci-dessous sont ceux qu'applique `privacy:purge`. Les dossiers
 * pédagogiques (élèves, tutelles, consentements, progression, attestations,
 * ijazas) et les pièces comptables (paiements, cotisations, trésorerie) ne
 * sont pas dans cette liste : les effacer automatiquement détruirait
 * l'historique de l'institut. Leur effacement passe par l'anonymisation
 * d'un élève, action explicite.
 */
final class RetentionPolicy
{
    /** Jetons de réinitialisation déjà expirés ou déjà utilisés. */
    public const PASSWORD_RESETS_DAYS = 7;

    /** Sessions HTTP (adresse IP, navigateur). */
    public const SESSIONS_DAYS = 30;

    /** Jetons d'API inactifs. */
    public const API_TOKENS_DAYS = 180;

    /** Notifications déjà lues. */
    public const NOTIFICATIONS_READ_DAYS = 180;

    /** Notifications jamais lues. */
    public const NOTIFICATIONS_UNREAD_DAYS = 365;

    /** Abonnements push sans utilisation. */
    public const PUSH_SUBSCRIPTIONS_DAYS = 180;

    /** Journal d'audit (adresse IP, navigateur). */
    public const AUDIT_LOGS_DAYS = 730;

    /** Demandes d'adhésion à un dahira déjà traitées (nom, téléphone). */
    public const JOIN_REQUESTS_DAYS = 365;

    /** Messages du chat d'un direct déjà archivé. */
    public const LIVE_CHAT_DAYS = 30;
}
