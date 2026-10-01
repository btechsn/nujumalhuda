<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

class AnalyticsSettingsPage extends SettingsSectionPage
{
    protected static ?string $navigationLabel = 'Google Analytics';

    protected static ?string $title = 'Google Analytics';

    protected static ?string $slug = 'parametres/analytics';

    protected static ?int $navigationSort = 50;

    protected static function section(): string
    {
        return 'analytics';
    }
}
