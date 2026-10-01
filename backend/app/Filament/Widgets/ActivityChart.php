<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Support\DashboardMetrics;
use Filament\Widgets\ChartWidget;

class ActivityChart extends ChartWidget
{
    protected static ?string $heading = 'Activité';

    protected static ?string $description = 'Comptes, inscriptions, questions et messages reçus.';

    protected static ?string $maxHeight = '16rem';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'xl' => 1,
    ];

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $metrics = DashboardMetrics::get();

        return [
            'datasets' => [
                [
                    'label' => 'Comptes',
                    'data' => $metrics['activity']['users'],
                    'borderColor' => '#1f8553',
                    'backgroundColor' => 'rgba(31, 133, 83, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Inscriptions',
                    'data' => $metrics['activity']['enrollments'],
                    'borderColor' => '#c8971f',
                    'backgroundColor' => 'rgba(200, 151, 31, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Questions',
                    'data' => $metrics['activity']['questions'],
                    'borderColor' => '#1d4e89',
                    'backgroundColor' => 'rgba(29, 78, 137, 0.1)',
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Messages',
                    'data' => $metrics['activity']['contacts'],
                    'borderColor' => '#9a3412',
                    'backgroundColor' => 'rgba(154, 52, 18, 0.1)',
                    'tension' => 0.35,
                ],
            ],
            'labels' => $metrics['labels'],
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
