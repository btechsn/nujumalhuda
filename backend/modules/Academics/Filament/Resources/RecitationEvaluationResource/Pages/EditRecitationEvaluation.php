<?php

namespace Modules\Academics\Filament\Resources\RecitationEvaluationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Academics\Filament\Resources\RecitationEvaluationResource;

class EditRecitationEvaluation extends EditRecord
{
    protected static string $resource = RecitationEvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
