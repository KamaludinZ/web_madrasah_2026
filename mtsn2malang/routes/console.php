<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Hapus data kunjungan lebih dari 90 hari (lihat PageView::prunable()).
// Butuh cron `php artisan schedule:run` tiap menit — lihat DEPLOYMENT.md.
Schedule::command('model:prune', ['--model' => [\App\Models\PageView::class]])->daily();
