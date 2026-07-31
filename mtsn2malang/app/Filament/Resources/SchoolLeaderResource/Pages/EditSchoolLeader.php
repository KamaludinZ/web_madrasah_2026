<?php

namespace App\Filament\Resources\SchoolLeaderResource\Pages;

use App\Filament\Resources\SchoolLeaderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSchoolLeader extends EditRecord
{
    protected static string $resource = SchoolLeaderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
