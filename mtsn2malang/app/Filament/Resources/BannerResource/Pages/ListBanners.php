<?php

namespace App\Filament\Resources\BannerResource\Pages;

use App\Filament\Resources\BannerResource;
use App\Models\Setting;
use App\Support\UploadImageHelper;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListBanners extends ListRecords
{
    protected static string $resource = BannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('slideDefault')
                ->label('Slide Default')
                ->icon('heroicon-o-photo')
                ->color('warning')
                ->modalWidth('4xl')
                ->form([
                    Forms\Components\TextInput::make('default_banner_name')
                        ->label('Nama Slide Default')
                        ->required()
                        ->default(fn () => Setting::get('default_banner_name', 'Slide Default'))
                        ->columnSpanFull(),

                    UploadImageHelper::webpFileUpload('default_banner_image', 'Gambar Slide Default', 'banners/default', true)
                        ->helperText('Gambar ini akan tampil ketika tidak ada banner aktif.')
                        ->default(fn () => Setting::get('default_banner_image'))
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('default_banner_subtitle')
                        ->label('Badge / Sub Judul')
                        ->placeholder('Contoh: Madrasah Unggulan')
                        ->helperText('Teks kecil yang tampil di atas judul utama (opsional).')
                        ->default(fn () => Setting::get('default_banner_subtitle', ''))
                        ->columnSpanFull(),

                    Forms\Components\RichEditor::make('default_banner_title')
                        ->label('Judul Utama Hero')
                        ->required()
                        ->toolbarButtons(['bold', 'italic', 'underline', 'strike', 'link', 'redo', 'undo'])
                        ->placeholder('Contoh: MTsN 2 Kota Malang')
                        ->helperText('Judul besar yang tampil di tengah hero section.')
                        ->default(fn () => Setting::get('default_banner_title', 'MTsN 2 Kota Malang'))
                        ->columnSpanFull(),

                    Forms\Components\RichEditor::make('default_banner_description')
                        ->label('Deskripsi / Sub Teks')
                        ->toolbarButtons(['bold', 'italic', 'underline', 'link', 'redo', 'undo'])
                        ->placeholder('Contoh: Unggul, Berkarakter, dan Berwawasan Global')
                        ->helperText('Teks pendukung di bawah judul (opsional).')
                        ->default(fn () => Setting::get('default_banner_description', 'Unggul, Berkarakter, dan Berwawasan Global'))
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('default_banner_link')
                        ->label('Link Tombol')
                        ->url()
                        ->placeholder('https://...')
                        ->helperText('URL tujuan tombol "Pelajari Lebih Lanjut". Kosongkan untuk menggunakan halaman Sambutan.')
                        ->default(fn () => Setting::get('default_banner_link', ''))
                        ->columnSpanFull(),
                ])
                ->action(function (array $data): void {
                    Setting::set('default_banner_name',        $data['default_banner_name']);
                    Setting::set('default_banner_image',       $data['default_banner_image'] ?? '');
                    Setting::set('default_banner_subtitle',    $data['default_banner_subtitle'] ?? '');
                    Setting::set('default_banner_title',       $data['default_banner_title'] ?? 'MTsN 2 Kota Malang');
                    Setting::set('default_banner_description', $data['default_banner_description'] ?? '');
                    Setting::set('default_banner_link',        $data['default_banner_link'] ?? '');

                    Notification::make()
                        ->title('Slide default berhasil disimpan')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua'),
            'active' => Tab::make('Aktif')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true)),
            'inactive' => Tab::make('Tidak Aktif')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false)),
        ];
    }
}
