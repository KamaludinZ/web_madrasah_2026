<?php

namespace App\Filament\Resources\TestimonialResource\Pages;

use App\Filament\Resources\TestimonialResource;
use App\Models\Setting;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListTestimonials extends ListRecords
{
    protected static string $resource = TestimonialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('publicDisplaySetting')
                ->label('Pengaturan Tampilan Publik')
                ->icon('heroicon-o-eye')
                ->color('warning')
                ->form([
                    Forms\Components\Toggle::make('show_testimonials_section')
                        ->label('Tampilkan Section Testimoni di Halaman Publik')
                        ->default(fn () => (bool) Setting::get('show_testimonials_section', true)),
                ])
                ->action(function (array $data): void {
                    Setting::set('show_testimonials_section', (bool) ($data['show_testimonials_section'] ?? true));

                    Notification::make()
                        ->title('Pengaturan tampilan testimoni berhasil disimpan')
                        ->success()
                        ->send();
                }),
        ];
    }
}
