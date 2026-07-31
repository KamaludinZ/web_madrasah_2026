<?php

namespace App\Filament\Resources\SchoolLeaderResource\Pages;

use App\Filament\Resources\SchoolLeaderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSchoolLeader extends CreateRecord
{
    protected static string $resource = SchoolLeaderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
