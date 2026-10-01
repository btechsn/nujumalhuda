<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

class OrangeMoneySettingsPage extends SettingsSectionPage
{
    protected static ?string $navigationLabel = 'Orange Money';

    protected static ?string $title = 'Paiement Orange Money';

    protected static ?string $slug = 'parametres/orange-money';

    protected static ?int $navigationSort = 90;

    protected static function section(): string
    {
        return 'orange_money';
    }
}
