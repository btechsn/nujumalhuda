<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

class SmsSettingsPage extends SettingsSectionPage
{
    protected static ?string $navigationLabel = 'SMS';

    protected static ?string $title = 'SMS';

    protected static ?string $slug = 'parametres/sms';

    protected static ?int $navigationSort = 70;

    protected static function section(): string
    {
        return 'sms';
    }
}
