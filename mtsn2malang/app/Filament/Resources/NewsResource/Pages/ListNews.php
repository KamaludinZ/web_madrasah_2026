<?php

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\NewsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListNews extends ListRecords
{
    protected static string $resource = NewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tulis Berita Baru'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua'),
            'draft' => Tab::make('Draf')->modifyQueryUsing(fn(Builder $q) => $q->where('status', 'draft')),
            'review' => Tab::make('Review')->modifyQueryUsing(fn(Builder $q) => $q->where('status', 'review')),
            'published' => Tab::make('Publikasi')->modifyQueryUsing(fn(Builder $q) => $q->where('status', 'published')),
            'archived' => Tab::make('Arsip')->modifyQueryUsing(fn(Builder $q) => $q->where('status', 'archived')),
        ];
    }
}
