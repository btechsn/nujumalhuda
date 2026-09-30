<?php

namespace Modules\Live\Filament\Resources\VodRecordingResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Live\Filament\Resources\VodRecordingResource;

class EditVodRecording extends EditRecord
{
    protected static string $resource = VodRecordingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
