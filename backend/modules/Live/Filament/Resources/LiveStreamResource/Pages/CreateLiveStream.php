<?php

namespace Modules\Live\Filament\Resources\LiveStreamResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Live\Filament\Resources\LiveStreamResource;

class CreateLiveStream extends CreateRecord
{
    protected static string $resource = LiveStreamResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
