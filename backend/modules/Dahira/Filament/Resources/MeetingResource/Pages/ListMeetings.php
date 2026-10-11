<?php

namespace Modules\Dahira\Filament\Resources\MeetingResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Dahira\Filament\Resources\MeetingResource;

class ListMeetings extends ListRecords
{
    protected static string $resource = MeetingResource::class;

    public function getTitle(): string
    {
        return 'Réunions';
    }

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
