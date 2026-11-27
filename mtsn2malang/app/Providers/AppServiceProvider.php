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

        // Filament 4/5: pertahankan perilaku Filament 3 yang diandalkan form &
        // tabel aplikasi ini (panduan upgrade resmi Filament v4):
        // - Grid/Section/Fieldset kembali selebar penuh form (bukan 1 kolom),
        // - filter tabel langsung diterapkan tanpa tombol "Terapkan",
        // - tabel tanpa urutan default tidak diurutkan otomatis per ID.
        \Filament\Schemas\Components\Fieldset::configureUsing(fn ($fieldset) => $fieldset->columnSpanFull());
        \Filament\Schemas\Components\Grid::configureUsing(fn ($grid) => $grid->columnSpanFull());
        \Filament\Schemas\Components\Section::configureUsing(fn ($section) => $section->columnSpanFull());
        \Filament\Tables\Table::configureUsing(fn ($table) => $table->deferFilters(false)->defaultKeySort(false));

        // Saat APP_DEBUG=false, Livewire mengubah TypeError di komponen menjadi
        // 419 "page expired" tanpa log apa pun. Laporkan supaya bisa dilacak.
        \Livewire\on('exception', function ($component, \Throwable $e) {
            if ($e instanceof \TypeError && ! config('app.debug')) {
                report($e);
            }
        });
    }
}
