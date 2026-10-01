<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Support\DashboardMetrics;
use Filament\Widgets\ChartWidget;

class SpacesChart extends ChartWidget
{
    protected static ?string $heading = 'Volume par espace';

    protected static ?string $description = 'Ce que chaque partie de l’institut contient aujourd’hui.';

    protected static ?string $maxHeight = '16rem';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'xl' => 1,
    ];

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $spaces = DashboardMetrics::get()['spaces'];

        return [
            'datasets' => [
                [
                    'label' => 'Éléments',
                    'data' => $spaces['values'],
                    'backgroundColor' => '#1f8553',
                    'borderRadius' => 6,
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $spaces['labels'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
