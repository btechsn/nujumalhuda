<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Support\DashboardMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Indicateurs';

    protected ?string $description = 'Situation actuelle et tendance des six derniers mois.';

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $kpis = DashboardMetrics::get()['kpis'];
        $donationsDelta = $this->delta($kpis['donations_month'], $kpis['donations_previous']);
        $usersDelta = $this->seriesDelta($kpis['users_series']);
        $studentsDelta = $this->seriesDelta($kpis['enrollments_series']);

        return [
            Stat::make('Comptes', $kpis['users'])
                ->description($usersDelta['label'])
                ->descriptionIcon($usersDelta['icon'])
                ->descriptionColor($usersDelta['color'])
                ->chart($kpis['users_series'])
                ->color('primary')
                ->icon('heroicon-o-users'),
            Stat::make('Élèves actifs', $kpis['students'])
                ->description($studentsDelta['label'])
                ->descriptionIcon($studentsDelta['icon'])
                ->descriptionColor($studentsDelta['color'])
                ->chart($kpis['enrollments_series'])
                ->color('primary')
                ->icon('heroicon-o-academic-cap'),
            Stat::make('Dons du mois', DashboardMetrics::money($kpis['donations_month']))
                ->description($donationsDelta['label'])
                ->descriptionIcon($donationsDelta['icon'])
                ->descriptionColor($donationsDelta['color'])
                ->chart($kpis['donations_series'])
                ->color('warning')
                ->icon('heroicon-o-banknotes'),
            Stat::make('Trésorerie', DashboardMetrics::money($kpis['treasury']))
                ->description('Solde des cercles, entrées moins sorties')
                ->color($kpis['treasury'] >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-scale'),
            Stat::make('Cotisations dues', $kpis['dues'])
                ->description($kpis['dues'] > 0 ? 'Échéances à relancer' : 'Aucune échéance ouverte')
                ->descriptionColor($kpis['dues'] > 0 ? 'warning' : 'success')
                ->color($kpis['dues'] > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-calendar-days'),
            Stat::make('Inscriptions', $kpis['pending_enrollments'])
                ->description($kpis['pending_enrollments'] > 0 ? 'Dossiers en attente' : 'File vide')
                ->descriptionColor($kpis['pending_enrollments'] > 0 ? 'warning' : 'success')
                ->chart($kpis['enrollments_series'])
                ->color('primary')
                ->icon('heroicon-o-clipboard-document-check'),
            Stat::make('En direct', $kpis['live'])
                ->description($kpis['live'] > 0 ? 'Diffusion en cours' : 'Aucun direct en cours')
                ->color($kpis['live'] > 0 ? 'danger' : 'gray')
                ->icon('heroicon-o-signal'),
            Stat::make('Nouveaux comptes', (int) (end($kpis['users_series']) ?: 0))
                ->description('Ce mois-ci')
                ->chart($kpis['users_series'])
                ->color('primary')
                ->icon('heroicon-o-user-plus'),
        ];
    }

    /**
     * @param  list<int>  $series
     * @return array{label: string, icon: string, color: string}
     */
    private function seriesDelta(array $series): array
    {
        $current = (int) (end($series) ?: 0);
        $previous = count($series) > 1 ? (int) $series[count($series) - 2] : 0;

        return $this->delta($current, $previous);
    }

    /**
     * @return array{label: string, icon: string, color: string}
     */
    private function delta(int $current, int $previous): array
    {
        $diff = $current - $previous;

        if ($previous === 0) {
            return [
                'label' => $diff > 0 ? 'Nouveau ce mois' : 'Stable par rapport au mois dernier',
                'icon' => $diff > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-minus',
                'color' => $diff > 0 ? 'success' : 'gray',
            ];
        }

        $percent = (int) round(($diff / $previous) * 100);

        return [
            'label' => ($percent > 0 ? '+' : '').$percent.' % vs mois dernier',
            'icon' => $diff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down',
            'color' => $diff >= 0 ? 'success' : 'danger',
        ];
    }
}
