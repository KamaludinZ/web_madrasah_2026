<?php

namespace App\Filament\Resources\HeadmasterHistoryResource\Pages;

use App\Filament\Resources\HeadmasterHistoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHeadmasterHistory extends CreateRecord
{
    protected static string $resource = HeadmasterHistoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
