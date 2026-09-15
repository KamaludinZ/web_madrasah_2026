<?php

namespace App\Filament\Widgets;

use App\Models\Comment;
use App\Models\News;
use App\Models\PageView;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class NewsStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $publishedCounts = News::published()
            ->where('published_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(published_at) as d, count(*) as aggregate')
            ->groupBy('d')
            ->pluck('aggregate', 'd');

        $weeklyChart = collect(range(6, 0))
            ->map(fn ($daysAgo) => (int) ($publishedCounts[now()->subDays($daysAgo)->toDateString()] ?? 0))
            ->toArray();

        return [
            Stat::make('Total Berita', News::published()->count())
                ->description('Berita terpublikasi')
                ->descriptionIcon('heroicon-m-newspaper')
                ->color('success')
                ->chart($weeklyChart),

            Stat::make('Draf Berita', News::where('status', 'draft')->count())
                ->description('Menunggu publikasi')
                ->descriptionIcon('heroicon-m-pencil')
                ->color('warning'),

            Stat::make('Pengunjung Hari Ini', PageView::todayCount())
                ->description('Pengunjung unik')
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),

            Stat::make('Total Pengunjung', number_format(PageView::totalCount()))
                ->description('Sejak website aktif')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Komentar Pending', Comment::where('status', 'pending')->count())
                ->description('Perlu moderasi')
                ->descriptionIcon('heroicon-m-chat-bubble-left')
                ->color('danger'),

            Stat::make('Berita Bulan Ini', News::published()->whereMonth('published_at', now()->month)->count())
                ->description('Bulan ' . now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success'),
        ];
    }
}
