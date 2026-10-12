<?php

declare(strict_types=1);

namespace Modules\Core\Actions;

use Modules\Core\Models\AuditLog;
use Modules\Core\Support\BackupCipher;
use RuntimeException;

/**
 * Déchiffre une sauvegarde en mémoire et vérifie qu'elle contient du SQL.
 * Le clair n'est pas écrit sur le disque et la base n'est pas écrasée.
 */
class VerifyEncryptedBackupAction
{
    /**
     * @return array{file: string, bytes: int}
     */
    public function execute(?string $path = null): array
    {
        $key = config('backup.encryption_key');
        if (! is_string($key) || strlen($key) < 16) {
            throw new RuntimeException('BACKUP_ENCRYPTION_KEY est absente ou trop courte. Le test de restauration est refusé.');
        }

        $file = $path ?? $this->latest();
        if ($file === null || ! is_file($file)) {
            throw new RuntimeException('Aucune sauvegarde chiffrée à vérifier.');
        }

        $payload = file_get_contents($file);
        if (! is_string($payload)) {
            throw new RuntimeException('Impossible de lire la sauvegarde.');
        }

        $plain = BackupCipher::open($payload, $key);
        $readable = str_contains($plain, 'INSERT INTO')
            || str_contains($plain, 'CREATE TABLE')
            || str_contains($plain, 'PostgreSQL database dump');

        $bytes = strlen($plain);
        unset($plain);

        if (! $readable) {
            throw new RuntimeException('La sauvegarde se déchiffre, mais elle ne contient pas de SQL reconnaissable.');
        }

        AuditLog::log('backup.restore_tested', context: [
            'file' => basename($file),
            'bytes' => $bytes,
            'ok' => true,
        ]);

        return ['file' => basename($file), 'bytes' => $bytes];
    }

    private function latest(): ?string
    {
        $files = glob(storage_path('app/backups/*.sql.enc')) ?: [];
        if ($files === []) {
            return null;
        }

        usort($files, fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));

        return $files[0];
    }
}
