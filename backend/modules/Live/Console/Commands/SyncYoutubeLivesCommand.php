<?php

declare(strict_types=1);

namespace Modules\Live\Console\Commands;

use Illuminate\Console\Command;
use Modules\Live\Services\SyncFinishedYoutubeLives;

class SyncYoutubeLivesCommand extends Command
{
    protected $signature = 'live:sync-youtube';

    protected $description = 'Termine les séances dont le direct YouTube est fini';

    public function handle(SyncFinishedYoutubeLives $sync): int
    {
        $closed = $sync->run();

        $this->info($closed === 0
            ? 'Aucun direct YouTube à clôturer.'
            : "{$closed} séance(s) terminée(s).");

        return self::SUCCESS;
    }
}
