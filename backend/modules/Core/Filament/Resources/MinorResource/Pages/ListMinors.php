<?php

declare(strict_types=1);

namespace Modules\Core\Filament\Resources\MinorResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Core\Filament\Resources\MinorResource;
use Modules\Core\Models\AuditLog;

class ListMinors extends ListRecords
{
    protected static string $resource = MinorResource::class;

    public function mount(): void
    {
        parent::mount();

        AuditLog::log('minors.listed');
    }

    public function getTitle(): string
    {
        return 'Élèves mineurs';
    }

    protected function getHeaderActions(): array
    {
        // Pas de création ici : un élève apparaît dès qu'un tuteur lui est
        // rattaché, via le module Tutelles.
        return [];
    }
}
