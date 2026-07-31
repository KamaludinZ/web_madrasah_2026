<?php

namespace App\Filament\Resources\ServiceStandardResource\Pages;

use App\Filament\Resources\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\ServiceStandardResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceStandard extends CreateRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = ServiceStandardResource::class;
}
