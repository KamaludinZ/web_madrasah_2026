<?php

namespace App\Filament\Resources\PortalQuickAccessResource\Pages;

use App\Filament\Resources\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\PortalQuickAccessResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePortalQuickAccess extends CreateRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = PortalQuickAccessResource::class;
}
