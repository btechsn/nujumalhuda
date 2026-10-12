<?php

declare(strict_types=1);

namespace Modules\Core\Actions;

use Modules\Core\Models\AuditLog;
use Modules\Core\Support\BackupCipher;
use Modules\Core\Support\DatabaseSqlDumper;
use RuntimeException;

class CreateEncryptedBackupAction
{
    public function execute(): string
    {
        $key = config('backup.encryption_key');
        if (!is_string($key) || strlen($key) < 16) {
            throw new RuntimeException('BACKUP_ENCRYPTION_KEY est absente ou trop courte (16 caractères minimum). La sauvegarde n\'est pas écrite en clair.');
        }

        $directory = storage_path('app/backups');
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException('Impossible de créer le dossier des sauvegardes.');
        }

        $stamp = now()->format('Y-m-d-His');
        $plain = $directory.DIRECTORY_SEPARATOR.'.tmp-'.$stamp.'.sql';
        $final = $directory.DIRECTORY_SEPARATOR.'nujumalhuda-'.$stamp.'.sql.enc';

        try {
            app(DatabaseSqlDumper::class)->write($plain);
            $sealed = BackupCipher::seal((string) file_get_contents($plain), $key);
            if (file_put_contents($final, $sealed) === false) {
                throw new RuntimeException('Impossible d\'enregistrer la sauvegarde chiffrée.');
            }
        } finally {
            if (is_file($plain)) {
                unlink($plain);
            }
        }

        $this->prune($directory, (int) config('backup.keep', 14));

        AuditLog::log('backup.created', context: ['file' => basename($final)]);

        return $final;
    }

    private function prune(string $directory, int $keep): void
    {
        $files = glob($directory.DIRECTORY_SEPARATOR.'*.sql.enc') ?: [];
        usort($files, fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));

        foreach (array_slice($files, max($keep, 1)) as $old) {
            unlink($old);
        }
    }
}
