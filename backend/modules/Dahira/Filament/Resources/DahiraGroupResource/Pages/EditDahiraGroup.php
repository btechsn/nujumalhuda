<?php

namespace Modules\Dahira\Filament\Resources\DahiraGroupResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Dahira\Filament\Resources\DahiraGroupResource;

class EditDahiraGroup extends EditRecord
{
    protected static string $resource = DahiraGroupResource::class;

    public function getTitle(): string
    {
        return 'Modifier le dahira';
    }

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
