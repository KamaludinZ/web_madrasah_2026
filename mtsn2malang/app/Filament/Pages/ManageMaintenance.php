<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;

class ManageMaintenance extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Mode Maintenance';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.pages.manage-maintenance';

    public bool $maintenanceMode = false;
    public ?string $endTime = null;
    public ?string $message = null;

    public function mount(): void
    {
        $this->maintenanceMode = app()->isDownForMaintenance();
        $this->endTime = Setting::get('maintenance_end_time');
        $this->message = Setting::get('maintenance_message', 'Website sedang dalam pemeliharaan. Kami akan segera kembali!');
    }

    public function enableMaintenance(): void
    {
        Setting::set('maintenance_end_time', $this->endTime);
        Setting::set('maintenance_message', $this->message);
        Artisan::call('down', ['--secret' => config('app.maintenance_secret', 'mtsn2-secret')]);
        $this->maintenanceMode = true;
        Notification::make()->title('Mode maintenance diaktifkan!')->warning()->send();
    }

    public function disableMaintenance(): void
    {
        Artisan::call('up');
        $this->maintenanceMode = false;
        Notification::make()->title('Website kembali online!')->success()->send();
    }
}
