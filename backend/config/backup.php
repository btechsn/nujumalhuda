<?php

declare(strict_types=1);

return [
    /*
     * Clé distincte de APP_KEY. À conserver aussi hors du serveur : si le
     * disque part avec la clé, le chiffrement ne protège plus la sauvegarde.
     */
    'encryption_key' => env('BACKUP_ENCRYPTION_KEY'),

    /*
     * Binaire pg_dump, si PostgreSQL est installé. Sinon la commande écrit
     * elle-même les lignes de chaque table.
     */
    'pg_dump' => env('BACKUP_PG_DUMP'),

    'keep' => 14,
];
