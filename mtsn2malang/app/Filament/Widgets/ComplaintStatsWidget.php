<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ComplaintResource;
use App\Filament\Resources\SaranResource;
use App\Models\Complaint;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ComplaintStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    /**
     * Saran, pengaduan, dan whistleblowing berada di satu tabel `complaints`,
     * dibedakan kolom `type`. "Masuk" = berstatus pending (belum ditindaklanjuti).
     */
    protected function getStats(): array
    {
        $masuk = fn (string $type) => Complaint::query()
            ->where('type', $type)
            ->where('status', 'pending')
            ->count();

        $total = fn (string $type) => Complaint::query()->where('type', $type)->count();

        return [
            Stat::make('Saran Masuk', $masuk('saran'))
                ->description('Dari ' . number_format($total('saran')) . ' total saran')
                ->descriptionIcon('heroicon-m-light-bulb')
                ->color('info')
                ->url(SaranResource::getUrl('index')),

            Stat::make('Pengaduan Masuk', $masuk('masyarakat'))
                ->description('Dari ' . number_format($total('masyarakat')) . ' total pengaduan')
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('warning')
                ->url(ComplaintResource::getUrl('index')),

            Stat::make('Whistleblowing Masuk', $masuk('whistleblowing'))
                ->description('Dari ' . number_format($total('whistleblowing')) . ' total laporan')
                ->descriptionIcon('heroicon-m-shield-exclamation')
                ->color('danger')
                ->url(ComplaintResource::getUrl('index')),
        ];
    }
}
