<?php

namespace App\Filament\Resources\FacilityResource\Pages;

use App\Filament\Resources\FacilityResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListFacilities extends ListRecords
{
    protected static string $resource = FacilityResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua'),
            'madrasah' => Tab::make('Sarpras Madrasah')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('category', '!=', 'mahad')),
            'mahad' => Tab::make("Sarpras Ma'had")
                ->modifyQueryUsing(fn(Builder $query) => $query->where('category', 'mahad')),
        ];
    }
}
