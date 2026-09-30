<?php

namespace Modules\Dahira\Filament\Resources\ContributionPlanResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Dahira\Filament\Resources\ContributionPlanResource;

class ListContributionPlans extends ListRecords
{
    protected static string $resource = ContributionPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
