<?php

namespace App\Filament\Widgets;

use App\Models\PageView;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class PageViewsWidget extends ChartWidget
{
    protected static ?string $heading = 'Statistik Pengunjung (30 Hari Terakhir)';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $counts = PageView::query()
            ->where('viewed_date', '>=', now()->subDays(29)->toDateString())
            ->selectRaw('viewed_date, count(*) as aggregate')
            ->groupBy('viewed_date')
            ->pluck('aggregate', 'viewed_date');

        $data = collect(range(29, 0))->map(function ($daysAgo) use ($counts) {
            $date = now()->subDays($daysAgo)->toDateString();
            return [
                'date' => Carbon::parse($date)->format('d M'),
                'count' => (int) ($counts[$date] ?? 0),
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Pengunjung',
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => 'rgba(26, 122, 60, 0.1)',
                    'borderColor' => '#1a7a3c',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $data->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
