<?php

declare(strict_types=1);

namespace App\Filament\Pages\Content;

final class DailyPage extends PlatformPage
{
    protected static function pageId(): string
    {
        return 'daily';
    }
}
