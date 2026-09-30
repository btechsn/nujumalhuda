<?php

namespace Modules\Academics\Filament\Resources\RecitationEvaluationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Academics\Filament\Resources\RecitationEvaluationResource;

class ListRecitationEvaluations extends ListRecords
{
    protected static string $resource = RecitationEvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
