<?php

namespace Modules\Mosque\Filament\Resources\IqamaAdjustmentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Mosque\Filament\Resources\IqamaAdjustmentResource;

class ListIqamaAdjustments extends ListRecords
{
    protected static string $resource = IqamaAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
