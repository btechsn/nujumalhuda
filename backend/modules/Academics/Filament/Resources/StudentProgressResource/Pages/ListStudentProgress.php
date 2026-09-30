<?php

namespace Modules\Academics\Filament\Resources\StudentProgressResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Academics\Filament\Resources\StudentProgressResource;

class ListStudentProgress extends ListRecords
{
    protected static string $resource = StudentProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
