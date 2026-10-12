<?php

declare(strict_types=1);

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Models\AuditLog;
use Modules\Core\Support\OperationsReport;

class OperationsStatusCommand extends Command
{
    protected $signature = 'operations:status';

    protected $description = 'Résume la sauvegarde, le disque et la qualification de la phase 3, sans noms ni téléphone.';

    public function handle(): int
    {
        $report = OperationsReport::collect();

        foreach ($report['lines'] as $line) {
            $this->line($line);
        }

        AuditLog::log('operations.checked', context: $report['flags']);

        return $report['ok'] ? self::SUCCESS : self::FAILURE;
    }
}
