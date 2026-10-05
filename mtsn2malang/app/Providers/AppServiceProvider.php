<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        // Saat APP_DEBUG=false, Livewire mengubah TypeError di komponen menjadi
        // 419 "page expired" tanpa log apa pun. Laporkan supaya bisa dilacak.
        \Livewire\on('exception', function ($component, \Throwable $e) {
            if ($e instanceof \TypeError && ! config('app.debug')) {
                report($e);
            }
        });
    }
}
