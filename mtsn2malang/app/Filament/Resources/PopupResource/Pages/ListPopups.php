<?php

namespace App\Filament\Resources\PopupResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\PopupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPopups extends ListRecords
{
    protected static string $resource = PopupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Buat Popup Baru'),
        ];
    }
}
