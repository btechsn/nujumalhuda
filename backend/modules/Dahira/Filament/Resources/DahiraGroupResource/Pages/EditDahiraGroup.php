<?php

namespace Modules\Dahira\Filament\Resources\DahiraGroupResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Dahira\Filament\Resources\DahiraGroupResource;

class EditDahiraGroup extends EditRecord
{
    protected static string $resource = DahiraGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
