<?php

namespace Modules\Dahira\Filament\Resources\ContributionScheduleResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Dahira\Filament\Resources\ContributionScheduleResource;

class EditContributionSchedule extends EditRecord
{
    protected static string $resource = ContributionScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
