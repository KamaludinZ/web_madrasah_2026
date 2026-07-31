<?php

namespace App\Filament\Resources\SocialMediaContentResource\Pages;

use App\Filament\Resources\SocialMediaContentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSocialMediaContent extends EditRecord
{
    protected static string $resource = SocialMediaContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
