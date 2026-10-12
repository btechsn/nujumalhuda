<?php

declare(strict_types=1);

namespace Modules\Announcements\Filament\Resources\AnnouncementResource\Pages;

use Modules\Announcements\Actions\BroadcastAnnouncementAction;
use Modules\Announcements\Enums\AudienceType;
use Modules\Announcements\Models\Announcement;

trait SyncsAnnouncementAudience
{
    protected function syncAudienceAndBroadcast(): void
    {
        /** @var Announcement $record */
        $record = $this->getRecord();
        $type = (string) ($this->data['audience_type'] ?? AudienceType::PUBLIC->value);
        $target = $this->data['audience_target'] ?? null;
        $needsTarget = in_array($type, ['role', 'organization', 'cohort'], true);

        $record->audiences()->delete();
        $record->audiences()->create([
            'type' => $type,
            'target_id' => $needsTarget && filled($target) ? $target : null,
        ]);

        app(BroadcastAnnouncementAction::class)->execute($record->fresh(['audiences']));
    }
}
