<?php

declare(strict_types=1);

namespace Modules\Announcements\Filament\Resources\AnnouncementResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Announcements\Enums\AudienceType;
use Modules\Announcements\Filament\Resources\AnnouncementResource;

class CreateAnnouncement extends CreateRecord
{
    protected static string $resource = AnnouncementResource::class;

    protected function afterCreate(): void
    {
        $this->getRecord()->audiences()->firstOrCreate([
            'type' => AudienceType::PUBLIC,
            'target_id' => null,
        ]);
    }
}
