<?php

namespace Modules\Academics\Filament\Resources\IjazaResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Academics\Filament\Resources\IjazaResource;

class EditIjaza extends EditRecord
{
    protected static string $resource = IjazaResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
