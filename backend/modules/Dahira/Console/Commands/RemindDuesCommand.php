<?php

namespace Modules\Dahira\Console\Commands;

use Illuminate\Console\Command;
use Modules\Dahira\Services\DahiraNotifier;

class RemindDuesCommand extends Command
{
    protected $signature = 'dahira:remind-dues';

    protected $description = 'Prépare les relances de cotisation. Le SMS est enregistré, pas envoyé.';

    public function handle(DahiraNotifier $notifier): int
    {
        $count = $notifier->remindDues();
        $this->info("{$count} relance(s) préparée(s). Les SMS ne partent pas tant que la passerelle Orange n'est pas branchée.");

        return self::SUCCESS;
    }
}
