<?php

namespace App\Filament\Resources\SocialMediaContentResource\Pages;

use App\Filament\Resources\SocialMediaContentResource;
use App\Models\Setting;
use App\Support\UploadImageHelper;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSocialMediaContents extends ListRecords
{
    protected static string $resource = SocialMediaContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('defaultThumbnail')
                ->label('Thumbnail Default Platform')
                ->icon('heroicon-o-photo')
                ->color('warning')
                ->form([
                    UploadImageHelper::webpFileUpload('default_social_thumbnail_youtube', 'Default Thumbnail YouTube', 'social-media/default', true)
                        ->default(fn () => Setting::get('default_social_thumbnail_youtube'))
                        ->helperText('Resolusi rekomendasi: 1280x720 (16:9, landscape).'),
                    UploadImageHelper::webpFileUpload('default_social_thumbnail_instagram', 'Default Thumbnail Instagram', 'social-media/default', true)
                        ->default(fn () => Setting::get('default_social_thumbnail_instagram'))
                        ->helperText('Resolusi rekomendasi: 1080x1350 (4:5, portrait).'),
                    UploadImageHelper::webpFileUpload('default_social_thumbnail_tiktok', 'Default Thumbnail TikTok', 'social-media/default', true)
                        ->default(fn () => Setting::get('default_social_thumbnail_tiktok'))
                        ->helperText('Resolusi rekomendasi: 1080x1920 (9:16, portrait).'),
                ])
                ->action(function (array $data): void {
                    Setting::set('default_social_thumbnail_youtube', $data['default_social_thumbnail_youtube'] ?? null);
                    Setting::set('default_social_thumbnail_instagram', $data['default_social_thumbnail_instagram'] ?? null);
                    Setting::set('default_social_thumbnail_tiktok', $data['default_social_thumbnail_tiktok'] ?? null);

                    Notification::make()
                        ->title('Thumbnail default platform berhasil disimpan')
                        ->success()
                        ->send();
                }),
        ];
    }
}
