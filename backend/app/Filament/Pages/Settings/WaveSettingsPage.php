<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

class WaveSettingsPage extends SettingsSectionPage
{
    protected static ?string $navigationLabel = 'Wave';

    protected static ?string $title = 'Paiement Wave';

    protected static ?string $slug = 'parametres/wave';

    protected static ?int $navigationSort = 80;

    protected static function section(): string
    {
        return 'wave';
    }
}
