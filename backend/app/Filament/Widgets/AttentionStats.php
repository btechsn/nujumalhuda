<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Support\DashboardMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AttentionStats extends StatsOverviewWidget
{
    protected static ?int $sort = 6;

    protected ?string $heading = 'À traiter';

    protected ?string $description = 'Ce qui attend une décision ou une réponse.';

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        return array_map(function (array $item): Stat {
            $waiting = $item['value'] > 0;

            return Stat::make($item['label'], $item['value'])
                ->description($waiting ? 'À examiner' : 'Rien en attente')
                ->descriptionColor($waiting ? 'warning' : 'success')
                ->color($waiting ? 'warning' : 'gray')
                ->icon($waiting ? 'heroicon-o-exclamation-circle' : 'heroicon-o-check-circle')
                ->url($item['url']);
        }, DashboardMetrics::get()['attention']);
    }
}
