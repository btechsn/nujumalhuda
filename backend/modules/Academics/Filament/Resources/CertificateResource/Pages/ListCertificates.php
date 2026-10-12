<?php

declare(strict_types=1);

namespace Modules\Academics\Filament\Resources\CertificateResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Academics\Filament\Resources\CertificateResource;

class ListCertificates extends ListRecords
{
    protected static string $resource = CertificateResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
