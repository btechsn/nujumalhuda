<?php

declare(strict_types=1);

namespace Modules\Live\Listeners;

use Modules\Core\Events\OperationsStatusCollecting;
use Modules\Live\Models\LiveStream;

class ReportLiveOperations
{
    public function handle(OperationsStatusCollecting $event): void
    {
        $event->flags['live_count'] = LiveStream::query()->where('status', 'live')->count();
    }
}
