<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\PortalSettings;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\HtmlString;

/**
 * Pengaturan Halaman Portal (menu admin Portal → Pengaturan Portal):
 * saklar aktif/nonaktif Portal, gambar latar penuh, dan transparansinya.
 */
class ManagePortal extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-paint-brush';
    protected static ?string $navigationLabel = 'Pengaturan Portal';
    protected static ?string $navigationGroup = 'Portal';
    protected static ?int    $navigationSort  = 3;
    protected static ?string $title           = 'Pengaturan Portal';
    protected static string  $view            = 'filament.pages.manage-portal';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'editor_in_chief']) ?? false;
    }

    /** Lencana "Mati" di sidebar saat Portal dinonaktifkan. */
    public static function getNavigationBadge(): ?string
    {
        return PortalSettings::enabled() ? null : 'Mati';
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    /** Lencana status Portal yang tersimpan beserta dampaknya di "/". */
    public static function statusBadge(): HtmlString
    {
        [$color, $label, $note] = PortalSettings::enabled()
            ? ['#15803d', 'Menyala', 'Alamat utama ( / ) menampilkan Halaman Portal.']
            : ['#b91c1c', 'Mati', 'Alamat utama ( / ) menampilkan Beranda. Menu Portal di header disembunyikan.'];

        return new HtmlString(
            '<span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold text-white" style="background:' . $color . '">'
            . '<span class="h-2 w-2 rounded-full bg-white" aria-hidden="true"></span>Portal ' . e($label) . '</span>'
            . '<span class="ms-2 text-sm text-gray-500 dark:text-gray-400">' . e($note) . '</span>'
        );
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            PortalSettings::KEY                         => PortalSettings::enabled(),
            PortalSettings::BACKGROUND_IMAGE_KEY        => Setting::get(PortalSettings::BACKGROUND_IMAGE_KEY),
            PortalSettings::BACKGROUND_TRANSPARENCY_KEY => PortalSettings::background()['transparency'],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Status Portal')
                ->description('Saat Portal dimatikan, alamat utama situs langsung menampilkan Beranda. Data Portal (kategori, link, Akses Cepat) tetap tersimpan.')
                ->icon('heroicon-o-power')
                ->schema([
                    // Status TERSIMPAN (bukan posisi saklar yang belum disimpan).
                    Forms\Components\Placeholder::make('portal_status')
                        ->label('Status saat ini')
                        ->content(fn () => static::statusBadge()),

                    Forms\Components\Toggle::make(PortalSettings::KEY)
                        ->label('Aktifkan Halaman Portal di alamat utama ( / )')
                        ->onColor('success')
                        ->offColor('danger')
                        ->onIcon('heroicon-m-check')
                        ->offIcon('heroicon-m-x-mark'),
                ]),

            Forms\Components\Section::make('Gambar Latar Halaman Portal')
                ->description('Gambar dipasang penuh di belakang seluruh isi Halaman Portal. Kosongkan untuk latar polos.')
                ->schema([
                    UploadImageHelper::webpFileUpload(PortalSettings::BACKGROUND_IMAGE_KEY, 'Gambar Latar', 'settings/portal')
                        ->helperText('Disarankan gambar lanskap minimal 1920×1080 px. JPG/PNG/WEBP, maks 5MB — otomatis dikonversi ke WEBP.')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make(PortalSettings::BACKGROUND_TRANSPARENCY_KEY)
                        ->label('Transparansi Gambar')
                        ->numeric()
                        ->integer()
                        ->minValue(0)
                        ->maxValue(100)
                        ->step(5)
                        ->suffix('%')
                        ->default(PortalSettings::DEFAULT_TRANSPARENCY)
                        ->required()
                        ->helperText('0% = gambar tampil penuh, 100% = tidak terlihat. Bawaan 80% agar teks tetap mudah dibaca.'),
                ]),
        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $enabled = (bool) $data[PortalSettings::KEY];
        if ($enabled !== PortalSettings::enabled()) {
            PortalSettings::setEnabled($enabled);
            $this->logActivity($enabled ? 'Halaman Portal diaktifkan' : 'Halaman Portal dinonaktifkan', ['portal_enabled' => $enabled]);
        }

        Setting::set(PortalSettings::BACKGROUND_IMAGE_KEY, $data[PortalSettings::BACKGROUND_IMAGE_KEY] ?: null);
        Setting::set(PortalSettings::BACKGROUND_TRANSPARENCY_KEY, (string) (int) $data[PortalSettings::BACKGROUND_TRANSPARENCY_KEY]);

        Notification::make()->title('Pengaturan Portal berhasil disimpan!')->success()->send();
    }

    /** Catat ke audit log (pola sama dengan ManageSettings); gagal mencatat tidak menggagalkan simpan. */
    private function logActivity(string $description, array $properties = []): void
    {
        try {
            if (! Schema::hasTable('activity_log')) {
                return;
            }
            activity()->causedBy(auth()->user())->withProperties($properties)->log($description);
        } catch (\Throwable $e) {
            Log::warning('Activity log error: ' . $e->getMessage());
        }
    }
}
