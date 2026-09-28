<?php

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\NewsResource;
use App\Models\News;
use App\Models\Setting;
use App\Support\UploadImageHelper;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListNews extends ListRecords
{
    protected static string $resource = NewsResource::class;

    protected function getTableQuery(): Builder
    {
        return NewsResource::getEloquentQuery();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tulis Berita Baru'),
            Actions\Action::make('defaultThumbnailNews')
                ->label('Thumbnail Default Berita')
                ->icon('heroicon-o-photo')
                ->color('warning')
                ->visible(fn () => NewsResource::canPublish())
                ->form([
                    UploadImageHelper::webpFileUpload('default_news_thumbnail', 'Default Thumbnail Berita', 'news/default', true)
                        ->default(fn () => Setting::get('default_news_thumbnail'))
                        ->helperText('Resolusi rekomendasi: 1280x720 (16:9, landscape).'),
                ])
                ->action(function (array $data): void {
                    Setting::set('default_news_thumbnail', $data['default_news_thumbnail'] ?? null);

                    Notification::make()
                        ->title('Thumbnail default berita berhasil disimpan')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all'       => Tab::make('Semua'),
            'draft'     => Tab::make('Draf')->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft')),
            'review'    => Tab::make('Review')->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'review')),
            'published' => Tab::make('Publikasi')->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'published')),
            'archived'  => Tab::make('Arsip')->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'archived')),
        ];
    }
}
