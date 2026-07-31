<?php

namespace App\Filament\Resources\PortalAppResource\Pages;

use App\Filament\Resources\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\PortalAppResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePortalApp extends CreateRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = PortalAppResource::class;
}
