<?php

namespace App\Filament\Resources\ExtracurricularResource\Pages;

use App\Filament\Resources\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\ExtracurricularResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExtracurricular extends CreateRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = ExtracurricularResource::class;
}
