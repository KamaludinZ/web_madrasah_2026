<?php

namespace App\Filament\Resources\PortalCategoryResource\Pages;

use App\Filament\Resources\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\PortalCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePortalCategory extends CreateRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = PortalCategoryResource::class;
}
