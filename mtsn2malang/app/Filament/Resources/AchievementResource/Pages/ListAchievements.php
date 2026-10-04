<?php

namespace App\Filament\Resources\AchievementResource\Pages;

use App\Filament\Resources\AchievementResource;
use App\Models\Setting;
use App\Services\AchievementImportService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListAchievements extends ListRecords
{
    protected static string $resource = AchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('publicDisplaySetting')
                ->label('Pengaturan Tampilan Publik')
                ->icon('heroicon-o-eye')
                ->color('warning')
                ->form([
                    Forms\Components\Toggle::make('show_achievements_section')
                        ->label('Tampilkan section "Ringkasan Prestasi" di halaman Beranda')
                        ->helperText('Bila dimatikan, ringkasan & daftar prestasi tidak tampil di Beranda. Data prestasi tetap tersimpan.')
                        ->default(fn () => (bool) Setting::get('show_achievements_section', true)),
                ])
                ->action(function (array $data): void {
                    Setting::set('show_achievements_section', (bool) ($data['show_achievements_section'] ?? true));

                    Notification::make()
                        ->title('Pengaturan tampilan prestasi berhasil disimpan')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('download_template_excel')
                ->label('Download Template Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn () => $this->downloadTemplateExcel()),

            Actions\Action::make('import_excel')
                ->label('Import dari Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('File Excel (.xlsx)')
                        ->required()
                        ->disk('local')
                        ->directory('imports/achievements')
                        ->visibility('private')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->maxSize(10240)
                        ->helperText('Gunakan template di atas agar kolom terbaca dengan benar. Maks 10MB.'),
                ])
                ->modalHeading('Import Data Prestasi dari Excel')
                ->modalSubmitActionLabel('Import')
                ->action(fn (array $data) => $this->importExcel($data)),

            Actions\CreateAction::make(),
        ];
    }

    protected function downloadTemplateExcel(): StreamedResponse
    {
        return response()->streamDownload(
            fn () => app(AchievementImportService::class)->writeTemplate(),
            'template-prestasi.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    protected function importExcel(array $data): void
    {
        $path = Storage::disk('local')->path($data['file']);

        $result = app(AchievementImportService::class)->import($path);

        Storage::disk('local')->delete($data['file']);

        $messages = [];

        if ($result['created'] > 0) {
            $messages[] = "{$result['created']} data prestasi berhasil diimpor.";
        }

        if (filled($result['warnings'])) {
            $messages[] = 'Peringatan:';
            $messages   = array_merge($messages, array_slice($result['warnings'], 0, 10));
        }

        if (filled($result['errors'])) {
            $messages[] = 'Gagal diimpor (' . count($result['errors']) . ' baris):';
            $messages   = array_merge($messages, array_slice($result['errors'], 0, 10));
        }

        $hasIssues = filled($result['errors']) || filled($result['warnings']);

        Notification::make()
            ->title($result['created'] > 0
                ? ($hasIssues ? 'Import selesai dengan catatan' : 'Import berhasil')
                : 'Tidak ada data yang berhasil diimpor')
            ->body(implode("\n", $messages) ?: 'File tidak berisi data yang bisa diimpor.')
            ->color($result['created'] > 0 ? ($hasIssues ? 'warning' : 'success') : 'danger')
            ->duration(15000)
            ->send();
    }
}
