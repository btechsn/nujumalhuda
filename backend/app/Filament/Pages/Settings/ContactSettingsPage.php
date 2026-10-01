<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

class ContactSettingsPage extends SettingsSectionPage
{
    protected static ?string $navigationLabel = 'Coordonnées';

    protected static ?string $title = 'Coordonnées';

    protected static ?string $slug = 'parametres/coordonnees';

    protected static ?int $navigationSort = 10;

    protected static function section(): string
    {
        return 'contact';
    }
}
