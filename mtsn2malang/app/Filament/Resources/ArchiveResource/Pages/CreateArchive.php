<?php

namespace App\Filament\Resources\ArchiveResource\Pages;

use App\Filament\Resources\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\ArchiveResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArchive extends CreateRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = ArchiveResource::class;
}
