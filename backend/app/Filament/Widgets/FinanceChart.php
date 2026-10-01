<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Support\DashboardMetrics;
use Filament\Widgets\ChartWidget;

class FinanceChart extends ChartWidget
{
    protected static ?string $heading = 'Évolution financière';

    protected static ?string $description = 'Dons, cotisations et mouvements de trésorerie, en XOF.';

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
                $this->series('Dons', $metrics['finance']['donations'], '#1f8553', 'rgba(31, 133, 83, 0.15)'),
                $this->series('Cotisations', $metrics['finance']['contributions'], '#c8971f', 'rgba(200, 151, 31, 0.15)'),
                $this->series('Entrées', $metrics['finance']['treasury_in'], '#0b5a31', 'rgba(11, 90, 49, 0.08)'),
                $this->series('Sorties', $metrics['finance']['treasury_out'], '#9a3412', 'rgba(154, 52, 18, 0.08)'),
            ],
            'labels' => $metrics['labels'],
        ];
    }

    /**
     * @param  list<int>  $data
     * @return array<string, mixed>
     */
    private function series(string $label, array $data, string $border, string $fill): array
    {
        return [
            'label' => $label,
            'data' => $data,
            'borderColor' => $border,
            'backgroundColor' => $fill,
            'fill' => true,
            'tension' => 0.35,
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
                ],
            ],
        ];
    }
}
