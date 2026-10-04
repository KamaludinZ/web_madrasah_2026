<?php

namespace App\Filament\Resources\PortalQuickAccessResource\Pages;

use App\Filament\Resources\PortalQuickAccessResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPortalQuickAccesses extends ListRecords
{
    protected static string $resource = PortalQuickAccessResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
