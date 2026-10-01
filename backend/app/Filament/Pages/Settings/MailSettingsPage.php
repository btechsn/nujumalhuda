<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

class MailSettingsPage extends SettingsSectionPage
{
    protected static ?string $navigationLabel = 'E-mail';

    protected static ?string $title = 'E-mail';

    protected static ?string $slug = 'parametres/email';

    protected static ?int $navigationSort = 60;

    protected static function section(): string
    {
        return 'mail';
    }
}
