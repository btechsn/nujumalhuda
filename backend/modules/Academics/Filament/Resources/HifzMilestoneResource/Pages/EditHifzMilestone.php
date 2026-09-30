<?php

namespace Modules\Academics\Filament\Resources\HifzMilestoneResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Academics\Filament\Resources\HifzMilestoneResource;

class EditHifzMilestone extends EditRecord
{
    protected static string $resource = HifzMilestoneResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
