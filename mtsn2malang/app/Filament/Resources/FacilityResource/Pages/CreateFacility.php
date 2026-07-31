<?php

namespace App\Filament\Resources\FacilityResource\Pages;

use App\Filament\Resources\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\FacilityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFacility extends CreateRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = FacilityResource::class;
}
