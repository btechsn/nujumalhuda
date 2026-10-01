<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\ActivityChart;
use App\Filament\Widgets\AttentionStats;
use App\Filament\Widgets\FinanceChart;
use App\Filament\Widgets\IncomeChart;
use App\Filament\Widgets\OverviewStats;
use App\Filament\Widgets\SpacesBoard;
use App\Filament\Widgets\SpacesChart;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Tableau de bord';

    protected static ?string $title = 'Tableau de bord';

    protected static ?int $navigationSort = -2;

    public function getSubheading(): string|Htmlable|null
    {
        return 'Chiffres, évolutions et points d’attention de l’institut.';
    }

    public function getColumns(): int|string|array
    {
        return [
            'default' => 1,
            'xl' => 2,
        ];
    }

    public function getWidgets(): array
    {
        return [
            OverviewStats::class,
            FinanceChart::class,
            ActivityChart::class,
            IncomeChart::class,
            SpacesChart::class,
            AttentionStats::class,
            SpacesBoard::class,
        ];
    }
}
