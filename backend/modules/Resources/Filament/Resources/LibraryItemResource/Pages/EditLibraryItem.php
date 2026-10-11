<?php

namespace Modules\Resources\Filament\Resources\LibraryItemResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Resources\Filament\Resources\LibraryItemResource;

class EditLibraryItem extends EditRecord
{
    protected static string $resource = LibraryItemResource::class;

    public function getTitle(): string
    {
        return 'Modifier la ressource';
    }

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
