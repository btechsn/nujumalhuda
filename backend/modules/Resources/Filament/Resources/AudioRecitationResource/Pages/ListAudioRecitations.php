<?php

namespace Modules\Resources\Filament\Resources\AudioRecitationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Resources\Filament\Resources\AudioRecitationResource;

class ListAudioRecitations extends ListRecords
{
    protected static string $resource = AudioRecitationResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}
