<?php

declare(strict_types=1);

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Actions\VerifyEncryptedBackupAction;
use RuntimeException;

class VerifyEncryptedBackupCommand extends Command
{
    protected $signature = 'backup:restore-test {path?}';

    protected $description = 'Déchiffre la dernière sauvegarde en mémoire et vérifie le SQL. N’écrit pas le clair et n’écrase pas la base.';

    public function handle(VerifyEncryptedBackupAction $verify): int
    {
        try {
            $result = $verify->execute($this->argument('path'));
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info($result['file'].' '.$result['bytes'].' octets');

        return self::SUCCESS;
    }
}
