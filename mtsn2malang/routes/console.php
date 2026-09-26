<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Ringkas detail kunjungan > 90 hari menjadi angka total (lihat
// PageView::pruneOld()). Butuh cron `php artisan schedule:run` tiap menit —
// lihat DEPLOYMENT.md.
Schedule::call(fn () => \App\Models\PageView::pruneOld())
    ->daily()
    ->name('ringkas-data-kunjungan')
    ->withoutOverlapping();
