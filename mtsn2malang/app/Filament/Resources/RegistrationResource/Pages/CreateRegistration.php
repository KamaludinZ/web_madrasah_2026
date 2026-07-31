<?php

namespace App\Filament\Resources\RegistrationResource\Pages;

use App\Filament\Resources\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\RegistrationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRegistration extends CreateRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = RegistrationResource::class;
}
