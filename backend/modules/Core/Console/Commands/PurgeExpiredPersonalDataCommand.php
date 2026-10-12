<?php

declare(strict_types=1);

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Actions\PurgeExpiredPersonalDataAction;
use Modules\Core\Events\PersonalDataPurgeRunning;

class PurgeExpiredPersonalDataCommand extends Command
{
    protected $signature = 'privacy:purge';

    protected $description = 'Efface les données personnelles dont la durée de conservation est dépassée (§8.3).';

    public function handle(PurgeExpiredPersonalDataAction $purge): int
    {
        $event = new PersonalDataPurgeRunning($purge->execute());
        event($event);

        foreach ($event->purged as $category => $count) {
            $this->line($category.': '.$count);
        }

        return self::SUCCESS;
    }
}
