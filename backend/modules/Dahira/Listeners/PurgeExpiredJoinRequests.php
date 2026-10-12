<?php

declare(strict_types=1);

namespace Modules\Dahira\Listeners;

use Modules\Core\Events\PersonalDataPurgeRunning;
use Modules\Core\Support\RetentionPolicy;
use Modules\Dahira\Models\DahiraJoinRequest;

class PurgeExpiredJoinRequests
{
    public function handle(PersonalDataPurgeRunning $event): void
    {
        $event->purged['dahira_join_requests'] = DahiraJoinRequest::query()
            ->whereIn('status', ['approved', 'rejected'])
            ->whereNotNull('reviewed_at')
            ->where('reviewed_at', '<', now()->subDays(RetentionPolicy::JOIN_REQUESTS_DAYS))
            ->delete();
    }
}
