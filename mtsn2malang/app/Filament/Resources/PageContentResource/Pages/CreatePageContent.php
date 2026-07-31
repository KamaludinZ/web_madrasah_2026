<?php

namespace App\Filament\Resources\PageContentResource\Pages;

use App\Filament\Resources\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\PageContentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePageContent extends CreateRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = PageContentResource::class;
}
