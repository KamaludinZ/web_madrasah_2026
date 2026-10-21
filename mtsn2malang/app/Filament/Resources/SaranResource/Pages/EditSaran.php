<?php

namespace App\Filament\Resources\SaranResource\Pages;

use App\Filament\Resources\SaranResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSaran extends EditRecord
{
    protected static string $resource = SaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /** Tanggapan untuk pengirim tanpa tanggal tindak lanjut → isi otomatis dengan waktu sekarang. */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (filled($data['public_response'] ?? null) && blank($data['responded_at'] ?? null)) {
            $data['responded_at'] = now();
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
