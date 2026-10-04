<?php

namespace App\Filament\Resources\PortalCategoryResource\Pages;

use App\Filament\Resources\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\PortalCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPortalCategory extends EditRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = PortalCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
