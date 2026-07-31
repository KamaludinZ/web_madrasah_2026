<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class ManageMaintenance extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Mode Maintenance';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.pages.manage-maintenance';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public bool $maintenanceMode = false;
    public ?string $endTime = null;
    public ?string $message = null;
    public ?string $bypassUrl = null;

    public function mount(): void
    {
        $this->maintenanceMode = app()->isDownForMaintenance();
        $this->endTime = Setting::get('maintenance_end_time');
        $this->message = Setting::get('maintenance_message', 'Website sedang dalam pemeliharaan. Kami akan segera kembali!');
        $this->bypassUrl = Setting::get('maintenance_bypass_url');
    }

    public function enableMaintenance(): void
    {
        Setting::set('maintenance_end_time', $this->endTime);
        Setting::set('maintenance_message', $this->message);

        $secret = trim((string) config('app.maintenance_secret', ''));
        if ($secret === '') {
            $secret = Str::random(32);
        }

        Artisan::call('down', ['--secret' => $secret]);

        $baseUrl = rtrim((string) config('app.url'), '/');
        $this->bypassUrl = $baseUrl . '/' . ltrim($secret, '/');
        Setting::set('maintenance_bypass_url', $this->bypassUrl);

        $this->maintenanceMode = true;

        Notification::make()
            ->title('Mode maintenance diaktifkan!')
            ->body('Bypass admin: ' . $this->bypassUrl . ' lalu akses /admin/login')
            ->warning()
            ->send();
    }

    public function disableMaintenance(): void
    {
        Artisan::call('up');
        $this->maintenanceMode = false;
        $this->bypassUrl = null;
        Setting::set('maintenance_bypass_url', null);

        Notification::make()->title('Website kembali online!')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('enableMaintenance')
                ->label('Aktifkan Maintenance')
                ->action('enableMaintenance')
                ->color('danger')
                ->icon('heroicon-o-exclamation-triangle')
                ->visible(fn (): bool => ! $this->maintenanceMode),

            \Filament\Actions\Action::make('disableMaintenance')
                ->label('Nonaktifkan Maintenance')
                ->action('disableMaintenance')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible(fn (): bool => $this->maintenanceMode),
        ];
    }
}
