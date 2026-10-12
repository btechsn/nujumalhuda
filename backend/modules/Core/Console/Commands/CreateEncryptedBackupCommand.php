<?php

declare(strict_types=1);

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Actions\CreateEncryptedBackupAction;

class CreateEncryptedBackupCommand extends Command
{
    protected $signature = 'backup:encrypted';

    protected $description = 'Écrit une sauvegarde chiffrée de la base (§8.3). Refuse de tourner sans clé dédiée.';

    public function handle(CreateEncryptedBackupAction $backup): int
    {
        $path = $backup->execute();
        $this->info($path);

        return self::SUCCESS;
    }
}
