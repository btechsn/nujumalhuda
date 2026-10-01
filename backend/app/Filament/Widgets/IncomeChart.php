<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Support\DashboardMetrics;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class IncomeChart extends ChartWidget
{
    protected static ?string $heading = 'D’où vient l’argent';

    protected static ?string $maxHeight = '16rem';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'xl' => 1,
    ];

    protected function getType(): string
    {
        return 'doughnut';
    }

    public function getDescription(): string|Htmlable|null
    {
        $income = DashboardMetrics::get()['income'];

        if (array_sum($income) === 0) {
            return 'Aucune entrée enregistrée sur les six derniers mois.';
        }

        return 'Répartition des entrées sur six mois.';
    }

    protected function getData(): array
    {
        $income = DashboardMetrics::get()['income'];

        return [
            'datasets' => [
                [
                    'data' => array_values($income),
                    'backgroundColor' => ['#1f8553', '#c8971f', '#0b5a31'],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => array_keys($income),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'x' => ['display' => false],
                'y' => ['display' => false],
            ],
        ];
    }
}
