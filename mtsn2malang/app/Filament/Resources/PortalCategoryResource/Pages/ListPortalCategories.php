<?php

namespace App\Filament\Resources\PortalCategoryResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\PortalCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPortalCategories extends ListRecords
{
    protected static string $resource = PortalCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
