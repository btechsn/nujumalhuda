<?php

namespace Modules\Dahira\Filament\Resources\ContributionPlanResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Dahira\Filament\Resources\ContributionPlanResource;

class EditContributionPlan extends EditRecord
{
    protected static string $resource = ContributionPlanResource::class;

    public function getTitle(): string
    {
        return 'Modifier le plan';
    }

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
