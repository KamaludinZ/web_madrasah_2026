<?php

namespace App\Filament\Resources\AlumniCoordinatorResource\Pages;

use App\Filament\Resources\AlumniCoordinatorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAlumniCoordinators extends ListRecords
{
    protected static string $resource = AlumniCoordinatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
