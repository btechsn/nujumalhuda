<?php

namespace Modules\Dahira\Filament\Resources\MeetingResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Dahira\Filament\Resources\MeetingResource;

class EditMeeting extends EditRecord
{
    protected static string $resource = MeetingResource::class;

    public function getTitle(): string
    {
        return 'Modifier la réunion';
    }

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
