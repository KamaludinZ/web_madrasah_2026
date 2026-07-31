<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageWelcome extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Sambutan & Profil';
    protected static ?string $navigationGroup = 'Konten';
    protected static ?int    $navigationSort  = 10;
    protected static string  $view            = 'filament.pages.manage-welcome';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'editor_in_chief']) ?? false;
    }

    public ?array $data = [];
    public string $activeTab = 'akademika';

    public function mount(): void
    {
        $this->form->fill([
            // Data Akademika
            'stat_students'  => Setting::get('stat_students', ''),
            'stat_teachers'  => Setting::get('stat_teachers', ''),
            'stat_staff'     => Setting::get('stat_staff', ''),
            'stat_alumni'    => Setting::get('stat_alumni', ''),

            // Sambutan Kepala
            'welcome_headmaster_photo'   => Setting::get('welcome_headmaster_photo', ''),
            'welcome_headmaster_name'    => Setting::get('welcome_headmaster_name', ''),
            'welcome_headmaster_period'  => Setting::get('welcome_headmaster_period', ''),
            'welcome_headmaster_message' => Setting::get('welcome_headmaster_message', ''),
            'welcome_headmaster_motto'   => Setting::get('welcome_headmaster_motto', ''),

            // Visi
            'visi' => Setting::get('visi', ''),

            // Misi (HTML dari RichEditor)
            'misi' => Setting::get('misi', ''),

            // Tujuan (HTML dari RichEditor)
            'tujuan' => Setting::get('tujuan', ''),

            // Identitas
            'identity_nomenklatur'    => Setting::get('identity_nomenklatur', 'Madrasah Tsanawiyah Negeri 2 Kota Malang'),
            'identity_npsn'           => Setting::get('identity_npsn', ''),
            'identity_nsm'            => Setting::get('identity_nsm', ''),
            'identity_akreditasi'     => Setting::get('identity_akreditasi', 'A'),
            'identity_address'        => Setting::get('identity_address', ''),
            'identity_website'        => Setting::get('identity_website', ''),
            'identity_adiwiyata'      => Setting::get('identity_adiwiyata', ''),
            'identity_zona_integritas'=> Setting::get('identity_zona_integritas', ''),
            'identity_npsn_url'       => Setting::get('identity_npsn_url', ''),
            'identity_nsm_url'        => Setting::get('identity_nsm_url', ''),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Sambutan & Profil')->tabs([

                // ── Tab 1: Data Akademika ─────────────────────────────────────
                Forms\Components\Tabs\Tab::make('Data Akademika')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Forms\Components\Section::make('Statistik Akademika')
                            ->description('Data ini akan ditampilkan sebagai angka statistik di bawah hero section halaman utama.')
                            ->schema([
                                Forms\Components\TextInput::make('stat_students')
                                    ->label('Jumlah Siswa')
                                    ->numeric()
                                    ->placeholder('1200')
                                    ->suffix('siswa'),
                                Forms\Components\TextInput::make('stat_teachers')
                                    ->label('Jumlah Guru')
                                    ->numeric()
                                    ->placeholder('80')
                                    ->suffix('guru'),
                                Forms\Components\TextInput::make('stat_staff')
                                    ->label('Jumlah Tenaga Kependidikan')
                                    ->numeric()
                                    ->placeholder('40')
                                    ->suffix('orang'),
                                Forms\Components\TextInput::make('stat_alumni')
                                    ->label('Jumlah Alumni')
                                    ->placeholder('5000+')
                                    ->helperText('Boleh menggunakan tanda + (contoh: 5000+)')
                                    ->suffix('alumni'),
                            ])->columns(2),
                    ]),

                // ── Tab 2: Sambutan Kepala ────────────────────────────────────
                Forms\Components\Tabs\Tab::make('Sambutan Kepala')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        Forms\Components\Section::make('Profil & Sambutan Kepala Madrasah')
                            ->schema([
                                UploadImageHelper::webpFileUpload('welcome_headmaster_photo', 'Foto Kepala Madrasah', 'settings/welcome')
                                    ->imageEditor()
                                    ->helperText('Foto resmi kepala madrasah untuk halaman sambutan. Otomatis dikonversi ke WebP.')
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('welcome_headmaster_name')
                                    ->label('Nama Kepala Madrasah')
                                    ->placeholder('Nama lengkap dengan gelar'),
                                Forms\Components\TextInput::make('welcome_headmaster_period')
                                    ->label('Periode Jabatan')
                                    ->placeholder('2023 – Sekarang'),
                                Forms\Components\RichEditor::make('welcome_headmaster_message')
                                    ->label('Kalimat Sambutan')
                                    ->toolbarButtons(['bold','italic','underline','bulletList','orderedList','undo','redo'])
                                    ->placeholder('Assalamu\'alaikum Wr. Wb. ...')
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('welcome_headmaster_motto')
                                    ->label('Motto / Kutipan')
                                    ->placeholder('"Unggul dalam Prestasi, Mulia dalam Akhlak"')
                                    ->columnSpanFull(),
                            ])->columns(2),
                    ]),

                // ── Tab 3: Visi – Misi – Tujuan ──────────────────────────────
                Forms\Components\Tabs\Tab::make('Visi – Misi – Tujuan')
                    ->icon('heroicon-o-light-bulb')
                    ->schema([
                        Forms\Components\Section::make('Visi')->schema([
                            Forms\Components\Textarea::make('visi')
                                ->label('Pernyataan Visi')
                                ->rows(3)
                                ->placeholder('Terwujudnya madrasah yang unggul, berkarakter, dan berwawasan global...')
                                ->columnSpanFull(),
                        ]),
                        Forms\Components\Section::make('Misi')->schema([
                            Forms\Components\RichEditor::make('misi')
                                ->label('Butir-butir Misi')
                                ->toolbarButtons(['bold', 'italic', 'underline', 'bulletList', 'orderedList', 'undo', 'redo'])
                                ->placeholder('Tuliskan butir-butir misi madrasah...')
                                ->helperText('Gunakan Ordered List (1,2,3) atau Bullet List untuk menampilkan butir-butir misi.')
                                ->columnSpanFull(),
                        ]),
                        Forms\Components\Section::make('Tujuan')->schema([
                            Forms\Components\RichEditor::make('tujuan')
                                ->label('Butir-butir Tujuan')
                                ->toolbarButtons(['bold', 'italic', 'underline', 'bulletList', 'orderedList', 'undo', 'redo'])
                                ->placeholder('Tuliskan butir-butir tujuan madrasah...')
                                ->helperText('Gunakan Ordered List (1,2,3) atau Bullet List untuk menampilkan butir-butir tujuan.')
                                ->columnSpanFull(),
                        ]),
                    ]),

                // ── Tab 4: Identitas ──────────────────────────────────────────
                Forms\Components\Tabs\Tab::make('Identitas')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Forms\Components\Section::make('Identitas Madrasah')
                            ->schema([
                                Forms\Components\TextInput::make('identity_nomenklatur')
                                    ->label('Nomenklatur Nama Madrasah')
                                    ->placeholder('Madrasah Tsanawiyah Negeri 2 Kota Malang')
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('identity_npsn')
                                    ->label('Nomor NPSN')
                                    ->placeholder('20584862'),
                                Forms\Components\TextInput::make('identity_nsm')
                                    ->label('Nomor NSM')
                                    ->placeholder('121135730002'),
                                Forms\Components\TextInput::make('identity_akreditasi')
                                    ->label('Akreditasi')
                                    ->placeholder('A (Unggul)'),
                                Forms\Components\TextInput::make('identity_address')
                                    ->label('Alamat Lengkap')
                                    ->placeholder('Jl. Raya Cemorokandang No. 77, Kota Malang'),
                                Forms\Components\TextInput::make('identity_website')
                                    ->label('Website Resmi')
                                    ->url()
                                    ->placeholder('https://mtsn2malang.sch.id'),
                                Forms\Components\TextInput::make('identity_adiwiyata')
                                    ->label('Predikat Adiwiyata')
                                    ->placeholder('Adiwiyata Nasional 2023'),
                                Forms\Components\TextInput::make('identity_zona_integritas')
                                    ->label('Predikat Zona Integritas')
                                    ->placeholder('Wilayah Bebas Korupsi (WBK) 2024'),
                                Forms\Components\TextInput::make('identity_npsn_url')
                                    ->label('Link Profil NPSN')
                                    ->url()
                                    ->placeholder('https://referensi.data.kemdikbud.go.id/tabs.php?npsn=...')
                                    ->helperText('URL profil sekolah di referensi.data.kemdikbud.go.id'),
                                Forms\Components\TextInput::make('identity_nsm_url')
                                    ->label('Link Profil NSM')
                                    ->url()
                                    ->placeholder('https://emispendis.kemenag.go.id/...')
                                    ->helperText('URL profil madrasah di EMIS Kemenag'),
                            ])->columns(2),
                    ]),

            ])->columnSpanFull(),
        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Misi & tujuan sekarang HTML string dari RichEditor, simpan langsung
        foreach ($data as $key => $value) {
            Setting::set($key, $value ?? '');
        }

        Notification::make()->title('Data sambutan & profil berhasil disimpan!')->success()->send();
    }

}
