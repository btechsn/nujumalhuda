<?php

namespace Modules\Mosque\Filament\Resources\IqamaAdjustmentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Mosque\Filament\Resources\IqamaAdjustmentResource;

class EditIqamaAdjustment extends EditRecord
{
    protected static string $resource = IqamaAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
