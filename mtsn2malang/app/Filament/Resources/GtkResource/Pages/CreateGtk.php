<?php

namespace App\Filament\Resources\GtkResource\Pages;

use App\Filament\Resources\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\GtkResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGtk extends CreateRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = GtkResource::class;
}
