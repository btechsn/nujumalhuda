<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Support\DashboardMetrics;
use Filament\Widgets\Widget;

class SpacesBoard extends Widget
{
    public static function canView(): bool
    {
        return \Modules\Core\Support\PanelAccess::allows('dashboard_money');
    }

    protected static ?int $sort = 7;

    protected static string $view = 'filament.widgets.spaces-board';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        return [
            'areas' => DashboardMetrics::get()['areas'],
        ];
    }
}
