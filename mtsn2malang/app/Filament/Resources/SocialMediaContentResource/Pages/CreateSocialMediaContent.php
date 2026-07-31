<?php

namespace App\Filament\Resources\SocialMediaContentResource\Pages;

use App\Filament\Resources\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\SocialMediaContentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSocialMediaContent extends CreateRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = SocialMediaContentResource::class;
}
