<?php

declare(strict_types=1);

namespace Modules\Announcements\Filament\Resources\AnnouncementResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Announcements\Filament\Resources\AnnouncementResource;
use Modules\Announcements\Models\Announcement;

class EditAnnouncement extends EditRecord
{
    protected static string $resource = AnnouncementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Announcement $record */
        $record = $this->getRecord();
        $data['title'] = $record->getTranslations('title');
        $data['message'] = $record->getTranslations('message');

        return $data;
    }
}
