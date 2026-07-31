<?php

namespace App\Filament\Resources\BannerResource\Pages;

use App\Filament\Resources\BannerResource;
use App\Models\Setting;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBanner extends EditRecord
{
    protected static string $resource = BannerResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['default_banner_image'] = Setting::get('default_banner_image');

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['default_banner_image'])) {
            Setting::set('default_banner_image', $data['default_banner_image']);
        }

        unset($data['default_banner_image']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
