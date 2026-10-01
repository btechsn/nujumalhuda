<?php

declare(strict_types=1);

namespace App\Filament\Pages\Content;

final class NewsPage extends PlatformPage
{
    protected static function pageId(): string
    {
        return 'news';
    }
}
