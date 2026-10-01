<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

class FooterSettingsPage extends SettingsSectionPage
{
    protected static ?string $navigationLabel = 'Pied de page';

    protected static ?string $title = 'Pied de page';

    protected static ?string $slug = 'parametres/pied-de-page';

    protected static ?int $navigationSort = 20;

    protected static function section(): string
    {
        return 'footer';
    }
}
