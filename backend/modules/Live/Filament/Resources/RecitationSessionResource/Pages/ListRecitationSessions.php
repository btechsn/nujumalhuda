<?php

declare(strict_types=1);

namespace Modules\Live\Filament\Resources\RecitationSessionResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Live\Filament\Resources\RecitationSessionResource;

class ListRecitationSessions extends ListRecords
{
    protected static string $resource = RecitationSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
