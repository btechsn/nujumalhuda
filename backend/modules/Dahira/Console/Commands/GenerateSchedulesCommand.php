<?php

namespace Modules\Dahira\Console\Commands;

use Illuminate\Console\Command;
use Modules\Dahira\Services\ScheduleGenerator;

class GenerateSchedulesCommand extends Command
{
    protected $signature = 'dahira:generate-schedules';

    protected $description = 'Ouvre les échéances de cotisation du mois pour les membres actifs';

    public function handle(ScheduleGenerator $generator): int
    {
        $created = $generator->generateForMonth();
        $this->info("{$created} échéance(s) créée(s).");

        return self::SUCCESS;
    }
}
