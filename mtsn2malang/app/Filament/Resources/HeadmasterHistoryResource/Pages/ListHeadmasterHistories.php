<?php

namespace App\Filament\Resources\HeadmasterHistoryResource\Pages;

use App\Filament\Resources\HeadmasterHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHeadmasterHistories extends ListRecords
{
    protected static string $resource = HeadmasterHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
