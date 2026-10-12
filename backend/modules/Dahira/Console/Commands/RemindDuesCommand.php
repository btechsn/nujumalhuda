<?php

namespace Modules\Dahira\Console\Commands;

use Illuminate\Console\Command;
use Modules\Dahira\Services\DahiraNotifier;

class RemindDuesCommand extends Command
{
    protected $signature = 'dahira:remind-dues';

    protected $description = 'Relance les cotisations. Le SMS part seulement si Orange est configuré.';

    public function handle(DahiraNotifier $notifier): int
    {
        $count = $notifier->remindDues();
        $this->info("{$count} relance(s) traitée(s). Sans identifiants Orange, le SMS reste en attente et apparaît dans Exploitation.");

        return self::SUCCESS;
    }
}
