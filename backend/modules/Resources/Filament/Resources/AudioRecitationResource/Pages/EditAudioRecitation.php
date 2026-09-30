<?php

namespace Modules\Resources\Filament\Resources\AudioRecitationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Resources\Filament\Resources\AudioRecitationResource;

class EditAudioRecitation extends EditRecord
{
    protected static string $resource = AudioRecitationResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\DeleteAction::make()];
    }
}
