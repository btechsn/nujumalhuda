<?php

namespace Modules\Resources\Filament\Resources\DailyContentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Resources\Filament\Resources\DailyContentResource;

class EditDailyContent extends EditRecord
{
    protected static string $resource = DailyContentResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\DeleteAction::make()];
    }
}
