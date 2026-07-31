<?php

namespace App\Filament\Resources\HeadmasterHistoryResource\Pages;

use App\Filament\Resources\HeadmasterHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHeadmasterHistory extends EditRecord
{
    protected static string $resource = HeadmasterHistoryResource::class;

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
