<?php

namespace Modules\Academics\Filament\Resources\IjazaResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Academics\Filament\Resources\IjazaResource;

class CreateIjaza extends CreateRecord
{
    protected static string $resource = IjazaResource::class;

    protected static ?string $title = 'Nouvelle ijaza';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return IjazaResource::enforceConsent($data);
    }
}
