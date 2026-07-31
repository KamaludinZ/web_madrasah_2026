<?php

namespace App\Filament\Resources\SchoolLeaderResource\Pages;

use App\Filament\Resources\SchoolLeaderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSchoolLeaders extends ListRecords
{
    protected static string $resource = SchoolLeaderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
