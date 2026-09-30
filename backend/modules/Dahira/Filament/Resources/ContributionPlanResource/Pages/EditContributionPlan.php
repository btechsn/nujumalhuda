<?php

namespace Modules\Dahira\Filament\Resources\ContributionPlanResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Dahira\Filament\Resources\ContributionPlanResource;

class EditContributionPlan extends EditRecord
{
    protected static string $resource = ContributionPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
