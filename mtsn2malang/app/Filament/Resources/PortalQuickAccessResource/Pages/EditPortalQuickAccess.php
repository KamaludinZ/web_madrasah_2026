<?php

namespace App\Filament\Resources\PortalQuickAccessResource\Pages;

use App\Filament\Resources\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\PortalQuickAccessResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPortalQuickAccess extends EditRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = PortalQuickAccessResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
