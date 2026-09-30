<?php

namespace Modules\Dahira\Filament\Resources\ContributionScheduleResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Dahira\Filament\Resources\ContributionScheduleResource;

class ListContributionSchedules extends ListRecords
{
    protected static string $resource = ContributionScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
