<?php

namespace App\Filament\Resources\PopupResource\Pages;

use App\Filament\Resources\PopupResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePopup extends CreateRecord
{
    protected static string $resource = PopupResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Pastikan item_type sesuai tipe popup
        return $data;
    }
}
