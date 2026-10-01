<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

class LinksSettingsPage extends SettingsSectionPage
{
    protected static ?string $navigationLabel = 'Liens';

    protected static ?string $title = 'Liens';

    protected static ?string $slug = 'parametres/liens';

    protected static ?int $navigationSort = 30;

    protected static function section(): string
    {
        return 'links';
    }
}
