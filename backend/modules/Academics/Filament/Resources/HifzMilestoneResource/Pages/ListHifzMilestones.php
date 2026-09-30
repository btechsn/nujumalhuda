<?php

namespace Modules\Academics\Filament\Resources\HifzMilestoneResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Academics\Filament\Resources\HifzMilestoneResource;

class ListHifzMilestones extends ListRecords
{
    protected static string $resource = HifzMilestoneResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
