<?php

namespace App\Filament\Pages;

use App\Jobs\ApplyManualUpdateJob;
use App\Jobs\ApplyUpdateJob;
use App\Jobs\CreateBackupJob;
use App\Jobs\RestoreBackupJob;
use App\Models\AppBackup;
use App\Models\AppRestore;
use App\Models\AppUpdate;
use App\Models\Setting;
use App\Services\BackupService;
use App\Services\UpdateService;
use App\Support\AppVersion;
use App\Support\ChunkedUpload;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Locked;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Spatie\Activitylog\Models\Activity;

class ManageSettings extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Pengaturan Website';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.manage-settings';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public ?array $data = [];

    /** Tab utama aktif: settings | log | audit | backup | update */
    public string $activeMainTab = 'settings';

    // ─── State untuk tab Backup & Restore ────────────────────────────────────

    /** File ZIP backup yang diunggah untuk direstore. */
    public ?TemporaryUploadedFile $restoreFile = null;

    /**
     * Token file backup yang diunggah bertahap (App\Support\ChunkedUpload) —
     * dipakai untuk backup besar yang melebihi batas upload sekali kirim.
     */
    #[Locked]
    public ?string $restoreUploadToken = null;

    #[Locked]
    public ?string $restoreUploadName = null;

    /** Restore dari backup yang sudah tersimpan di server (tanpa upload). */
    #[Locked]
    public ?int $restoreFromBackupId = null;

    /** Konfirmasi mengetik "RESTORE" sebelum tombol restore aktif. */
    public string $restoreConfirmText = '';

    public bool $showRestoreConfirmModal = false;

    // ─── State untuk tab Update Aplikasi ─────────────────────────────────────

    public string $updateGithubRepo = '';

    /** Kosong = token tersimpan tidak diubah. Diisi hanya saat mengganti token. */
    public string $updateGithubTokenInput = '';

    public ?array $updateCheckResult = null;

    public bool $updateConfirm = false;

    /** File ZIP kode aplikasi untuk update manual (tanpa GitHub). */
    public ?TemporaryUploadedFile $manualUpdateFile = null;

    public string $manualUpdateVersion = '';

    public bool $manualUpdateConfirm = false;

    public bool $showManualUpdateConfirmModal = false;

    // ─── State untuk tab Log & Audit ─────────────────────────────────────────

    /** Filter level log: all | error | warning | info */
    public string $logFilter = 'all';

    /** Jumlah baris log yang ditampilkan */
    public int $logLines = 100;

    /** Filter audit: nama causer */
    public string $auditSearch = '';

    /** Filter audit: log name */
    public string $auditLogName = '';

    /** Halaman audit */
    public int $auditPage = 1;

    // ─── Mount ───────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->form->fill([
            'site_name'              => Setting::get('site_name', 'MTsN 2 Kota Malang'),
            'site_tagline'           => Setting::get('site_tagline', 'Unggul, Berkarakter, dan Berwawasan Global'),
            'logo'                   => Setting::get('logo', ''),
            'favicon'                => Setting::get('favicon', ''),
            'announcement_text'      => Setting::get('announcement_text', ''),
            'announcement_speed'     => Setting::get('announcement_speed', 30),
            'academic_year'          => Setting::get('academic_year', '2024/2025'),
            'google_analytics_id'    => Setting::get('google_analytics_id', ''),
            'recaptcha_site_key'     => Setting::get('recaptcha_site_key', ''),
            // Rahasia tidak pernah dikirim ke browser — kosong = tidak diubah.
            'gemini_api_key'         => '',
            'openai_api_key'         => '',
            'ai_provider'            => Setting::get('ai_provider', 'gemini'),
            'link_sippn'             => Setting::get('link_sippn', 'https://sippn.menpan.go.id/'),
            'link_ptsp'              => Setting::get('link_ptsp', 'https://ptsp.kemenag.go.id/'),
            'link_span_lapor'        => Setting::get('link_span_lapor', 'https://www.lapor.go.id/'),
            'top_header_address'     => Setting::get('top_header_address', 'Jl. Raya Cemorokandang No. 77 Kota Malang'),
            'top_header_maps_url'    => Setting::get('top_header_maps_url', 'https://maps.app.goo.gl/RHV4nKSg1eKe3qgo8'),
            'top_header_phone'       => Setting::get('top_header_phone', '(0341) 711500'),
            'top_header_phone_link'  => Setting::get('top_header_phone_link', '0341711500'),
            'top_header_wa_label'    => Setting::get('top_header_wa_label', '0851'),
            'top_header_wa_url'      => Setting::get('top_header_wa_url', 'https://wa.me/6285163350000'),
            'top_header_email'       => Setting::get('top_header_email', 'mtsnmalang2adm@gmail.com'),
            'social_youtube'          => Setting::get('social_youtube', 'https://www.youtube.com/channel/UCsqzV9zkTBlYZfmOA-_Y63A'),
            'social_instagram'        => Setting::get('social_instagram', 'https://www.instagram.com/mtsn2kota_malang'),
            'social_facebook'         => Setting::get('social_facebook', 'https://web.facebook.com/mtsn2kotamalang'),
            'social_tiktok'           => Setting::get('social_tiktok', 'https://www.tiktok.com/@mtsn2kotamalang'),
            'social_whatsapp_channel' => Setting::get('social_whatsapp_channel', ''),
            'whatsapp_number'         => Setting::get('whatsapp_number', ''),
            'footer_text'            => Setting::get('footer_text', ''),
            'headmaster_photo'       => Setting::get('headmaster_photo', ''),
            'headmaster_name'        => Setting::get('headmaster_name', ''),
            'headmaster_nip'         => Setting::get('headmaster_nip', ''),
            'headmaster_period'      => Setting::get('headmaster_period', ''),
            'login_otp_enabled'      => (bool) Setting::get('login_otp_enabled', false),

            // Email / SMTP
            'mail_driver'            => Setting::get('mail_driver', 'smtp'),
            'mail_host'              => Setting::get('mail_host', 'smtp.gmail.com'),
            'mail_port'              => Setting::get('mail_port', '587'),
            'mail_username'          => Setting::get('mail_username', ''),
            'mail_password'          => '',
            'mail_encryption'        => Setting::get('mail_encryption', 'tls'),
            'mail_from_address'      => Setting::get('mail_from_address', ''),
            'mail_from_name'         => Setting::get('mail_from_name', 'MTsN 2 Kota Malang'),

            // Kontak Madrasah
            'contact_address'        => Setting::get('contact_address', 'Jl. Raya Cemorokandang No. 77, Kota Malang, Jawa Timur 65138'),
            'contact_maps_url'       => Setting::get('contact_maps_url', 'https://maps.app.goo.gl/RHV4nKSg1eKe3qgo8'),
            'contact_maps_embed'     => Setting::get('contact_maps_embed', ''),
            'contact_phone'          => Setting::get('contact_phone', '(0341) 711500'),
            'contact_phone_link'     => Setting::get('contact_phone_link', '0341711500'),
            'contact_email'          => Setting::get('contact_email', 'mtsnmalang2adm@gmail.com'),
            'contact_wa_ptsp'        => Setting::get('contact_wa_ptsp', ''),
            'contact_wa_pengaduan'   => Setting::get('contact_wa_pengaduan', ''),
            'contact_wa_komite'      => Setting::get('contact_wa_komite', ''),
            'contact_hours'          => Setting::get('contact_hours', "Senin – Kamis : 07.00 – 15.00 WIB\nJumat : 07.00 – 11.30 WIB\nSabtu – Minggu : Tutup"),

            // Kontak Ma'had
            'mahad_name'             => Setting::get('mahad_name', "Ma'had Al-Ittihad MTsN 2 Kota Malang"),
            'mahad_address'          => Setting::get('mahad_address', ''),
            'mahad_maps_url'         => Setting::get('mahad_maps_url', ''),
            'mahad_maps_embed'       => Setting::get('mahad_maps_embed', ''),
            'mahad_phone'            => Setting::get('mahad_phone', ''),
            'mahad_phone_link'       => Setting::get('mahad_phone_link', ''),
            'mahad_email'            => Setting::get('mahad_email', ''),
            'mahad_wa'               => Setting::get('mahad_wa', ''),
            'mahad_wa_pengaduan'     => Setting::get('mahad_wa_pengaduan', ''),
            'mahad_wa_komite'        => Setting::get('mahad_wa_komite', ''),
            'mahad_hours'            => Setting::get('mahad_hours', "Senin – Jumat : 07.00 – 16.00 WIB\nSabtu : 07.00 – 12.00 WIB"),

            // Sosial Media Ma'had
            'mahad_social_youtube'   => Setting::get('mahad_social_youtube', ''),
            'mahad_social_instagram'    => Setting::get('mahad_social_instagram', ''),
            'mahad_social_facebook'     => Setting::get('mahad_social_facebook', ''),
            'mahad_social_tiktok'       => Setting::get('mahad_social_tiktok', ''),
            'mahad_wa_channel'          => Setting::get('mahad_wa_channel', ''),
        ]);

        $this->updateGithubRepo = app(UpdateService::class)->repo() ?? '';
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Pengaturan')->tabs([

                // ── Tab 1: Umum ──────────────────────────────────────────────
                Forms\Components\Tabs\Tab::make('Umum')->schema([
                    Forms\Components\TextInput::make('site_name')
                        ->label('Nama Website')
                        ->required(),
                    Forms\Components\TextInput::make('site_tagline')
                        ->label('Tagline'),
                    UploadImageHelper::webpFileUpload('logo', 'Logo Website', 'settings/branding')
                        ->imageEditor()
                        ->helperText('Logo utama untuk header/footer. Otomatis dikonversi ke WebP (maks 5MB).'),
                    UploadImageHelper::webpFileUpload('favicon', 'Favicon Website', 'settings/branding')
                        ->helperText('Ikon tab browser. Otomatis dikonversi ke WebP (maks 5MB).'),
                    Forms\Components\Textarea::make('announcement_text')
                        ->label('Teks Pengumuman Berjalan')
                        ->rows(2)
                        ->helperText('Teks yang berjalan di bawah header'),
                    Forms\Components\TextInput::make('announcement_speed')
                        ->label('Kecepatan Running Text (detik)')
                        ->numeric()
                        ->minValue(5)
                        ->maxValue(120)
                        ->default(30)
                        ->helperText('Semakin kecil angka, semakin cepat teks berjalan. Rekomendasi: 20-40 detik.'),
                    Forms\Components\TextInput::make('academic_year')
                        ->label('Tahun Pelajaran Aktif')
                        ->placeholder('2024/2025'),
                    Forms\Components\TextInput::make('whatsapp_number')
                        ->label('Nomor WhatsApp PTSP'),
                    Forms\Components\Textarea::make('footer_text')
                        ->label('Teks Footer')
                        ->rows(2),
                ]),

                // ── Tab 2: Header & Footer ────────────────────────────────────
                Forms\Components\Tabs\Tab::make('Header & Footer')->schema([
                    Forms\Components\Section::make('Top Header')
                        ->schema([
                            Forms\Components\TextInput::make('top_header_address')
                                ->label('Alamat Top Header'),
                            Forms\Components\TextInput::make('top_header_maps_url')
                                ->label('Link Google Maps')
                                ->url(),
                            Forms\Components\TextInput::make('top_header_phone')
                                ->label('Nomor Telepon (Teks)')
                                ->placeholder('(0341) 711500'),
                            Forms\Components\TextInput::make('top_header_phone_link')
                                ->label('Nomor Telepon (Link tel:)')
                                ->placeholder('0341711500'),
                            Forms\Components\TextInput::make('top_header_wa_label')
                                ->label('Label WhatsApp')
                                ->placeholder('0851'),
                            Forms\Components\TextInput::make('top_header_wa_url')
                                ->label('Link WhatsApp')
                                ->url(),
                            Forms\Components\TextInput::make('top_header_email')
                                ->label('Email Top Header')
                                ->email(),
                        ])->columns(2),
                    Forms\Components\Section::make('Sosial Media (Header & Footer)')
                        ->schema([
                            Forms\Components\TextInput::make('social_youtube')
                                ->label('YouTube URL')
                                ->url(),
                            Forms\Components\TextInput::make('social_instagram')
                                ->label('Instagram URL')
                                ->url(),
                            Forms\Components\TextInput::make('social_facebook')
                                ->label('Facebook URL')
                                ->url(),
                            Forms\Components\TextInput::make('social_tiktok')
                                ->label('TikTok URL')
                                ->url(),
                            Forms\Components\TextInput::make('social_whatsapp_channel')
                                ->label('WhatsApp Channel URL')
                                ->url()
                                ->placeholder('https://whatsapp.com/channel/...')
                                ->helperText('Link channel WhatsApp resmi madrasah. Akan ditampilkan dengan ikon WhatsApp di header dan footer.'),
                        ])->columns(2),
                    Forms\Components\Section::make('Link Menu Eksternal')
                        ->description('URL untuk menu eksternal yang tampil di navigasi publik dan footer.')
                        ->schema([
                            Forms\Components\TextInput::make('link_sippn')
                                ->label('Link SIPPN')
                                ->url()
                                ->placeholder('https://sippn.menpan.go.id/')
                                ->helperText('Tampil di menu Layanan (desktop & mobile) dan footer.'),
                            Forms\Components\TextInput::make('link_ptsp')
                                ->label('Link PTSP Online')
                                ->url()
                                ->placeholder('https://ptsp.kemenag.go.id/')
                                ->helperText('Tampil di menu Layanan (desktop & mobile) dan footer.'),
                            Forms\Components\TextInput::make('link_span_lapor')
                                ->label('Link SPAN Lapor')
                                ->url()
                                ->placeholder('https://www.lapor.go.id/')
                                ->helperText('Tampil di menu Pengaduan (desktop & mobile) dan footer.'),
                        ])->columns(3),
                    Forms\Components\Section::make('Footer')
                        ->schema([
                            Forms\Components\Textarea::make('footer_text')
                                ->label('Teks Footer')
                                ->rows(2),
                        ]),
                ]),

                // ── Tab 3: Kepala Madrasah ────────────────────────────────────
                Forms\Components\Tabs\Tab::make('Kepala Madrasah')->schema([
                    Forms\Components\Section::make('Profil Kepala Madrasah')
                        ->schema([
                            UploadImageHelper::webpFileUpload('headmaster_photo', 'Foto Kepala Madrasah', 'settings/headmaster')
                                ->imageEditor()
                                ->helperText('Upload foto resmi kepala madrasah. Otomatis dikonversi ke WebP (maks 5MB).'),
                            Forms\Components\TextInput::make('headmaster_name')
                                ->label('Nama Kepala Madrasah')
                                ->placeholder('Nama lengkap kepala madrasah'),
                            Forms\Components\TextInput::make('headmaster_nip')
                                ->label('NIP Kepala Madrasah')
                                ->placeholder('Contoh: 197001011995031001'),
                            Forms\Components\TextInput::make('headmaster_period')
                                ->label('Periode Jabatan')
                                ->placeholder('Contoh: 2023 - Sekarang'),
                        ])->columns(2),
                ]),

                // ── Tab 4: AI & SEO ───────────────────────────────────────────
                Forms\Components\Tabs\Tab::make('AI & SEO')->schema([

                    // Panduan AI
                    Forms\Components\Placeholder::make('ai_guide')
                        ->label('')
                        ->content(new \Illuminate\Support\HtmlString('
                            <div class="rounded-xl border border-violet-200 bg-violet-50 dark:bg-violet-900/20 dark:border-violet-800 p-4 text-sm space-y-3">
                                <p class="font-semibold text-violet-800 dark:text-violet-300 flex items-center gap-2">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                    Panduan Konfigurasi AI
                                </p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <p class="font-medium text-violet-700 dark:text-violet-300">🤖 Google Gemini (Rekomendasi — Gratis)</p>
                                        <ul class="text-xs space-y-0.5 text-violet-600 dark:text-violet-400 list-disc list-inside">
                                            <li>Buka <strong>aistudio.google.com</strong></li>
                                            <li>Login dengan akun Google</li>
                                            <li>Klik <strong>Get API Key → Create API Key</strong></li>
                                            <li>Salin API Key dan tempel di field Gemini API Key</li>
                                            <li>Gratis hingga batas tertentu per bulan</li>
                                        </ul>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="font-medium text-violet-700 dark:text-violet-300">🧠 OpenAI (Berbayar)</p>
                                        <ul class="text-xs space-y-0.5 text-violet-600 dark:text-violet-400 list-disc list-inside">
                                            <li>Buka <strong>platform.openai.com</strong></li>
                                            <li>Login → API Keys → Create new secret key</li>
                                            <li>Salin API Key (hanya tampil sekali)</li>
                                            <li>Pastikan saldo kredit mencukupi</li>
                                            <li>Model yang digunakan: gpt-4o-mini</li>
                                        </ul>
                                    </div>
                                </div>
                                <p class="text-xs text-violet-600 dark:text-violet-400 border-t border-violet-200 dark:border-violet-700 pt-2">
                                    💡 Fitur AI digunakan untuk: Generate ringkasan berita, Generate SEO meta title/description, dan Buat draf konten berita otomatis.
                                </p>
                            </div>
                        '))
                        ->columnSpanFull(),

                    // Form AI
                    Forms\Components\Section::make('Konfigurasi AI')
                        ->schema([
                            Forms\Components\Select::make('ai_provider')
                                ->label('Provider AI Aktif')
                                ->options(['gemini' => 'Google Gemini', 'openai' => 'OpenAI GPT'])
                                ->default('gemini')
                                ->helperText('Provider yang dipilih akan digunakan untuk semua fitur AI di sistem.')
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('gemini_api_key')
                                ->label('Gemini API Key')
                                ->password()
                                ->revealable()
                                ->placeholder(fn () => filled(Setting::get('gemini_api_key')) ? '•••••••• (tersimpan — kosongkan jika tidak diubah)' : 'AIza...')
                                ->helperText('Dapatkan di: aistudio.google.com → Get API Key'),
                            Forms\Components\TextInput::make('openai_api_key')
                                ->label('OpenAI API Key')
                                ->password()
                                ->revealable()
                                ->placeholder(fn () => filled(Setting::get('openai_api_key')) ? '•••••••• (tersimpan — kosongkan jika tidak diubah)' : 'sk-...')
                                ->helperText('Dapatkan di: platform.openai.com → API Keys'),
                        ])->columns(2),

                    // Tombol tes AI
                    Forms\Components\Section::make('Uji Koneksi AI')
                        ->description('Simpan API Key terlebih dahulu, lalu klik tombol di bawah untuk memverifikasi koneksi ke provider AI yang dipilih.')
                        ->schema([
                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('test_ai_connection')
                                    ->label('Tes Koneksi AI')
                                    ->icon('heroicon-o-cpu-chip')
                                    ->color('warning')
                                    ->action('testAiConnection'),
                            ])->columnSpanFull(),
                        ]),

                    // SEO & Analytics
                    Forms\Components\Section::make('SEO & Analytics')
                        ->schema([
                            Forms\Components\TextInput::make('google_analytics_id')
                                ->label('Google Analytics ID')
                                ->placeholder('G-XXXXXXXXXX')
                                ->helperText('Dapatkan di: analytics.google.com → Admin → Data Streams → Measurement ID'),
                            Forms\Components\TextInput::make('recaptcha_site_key')
                                ->label('reCAPTCHA Site Key')
                                ->helperText('Dapatkan di: google.com/recaptcha → Admin Console → Site Key'),
                        ])->columns(2),
                ]),

                // ── Tab 5: Kontak Madrasah ────────────────────────────────────
                Forms\Components\Tabs\Tab::make('Kontak Madrasah')->schema([
                    Forms\Components\Section::make('Informasi Kontak')
                        ->schema([
                            Forms\Components\Textarea::make('contact_address')
                                ->label('Alamat Lengkap')
                                ->rows(2)
                                ->placeholder('Jl. Raya Cemorokandang No. 77, Kota Malang, Jawa Timur 65138')
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('contact_phone')
                                ->label('Nomor Telepon (Teks)')
                                ->placeholder('(0341) 711500'),
                            Forms\Components\TextInput::make('contact_phone_link')
                                ->label('Nomor Telepon (Link tel:)')
                                ->placeholder('0341711500'),
                            Forms\Components\TextInput::make('contact_email')
                                ->label('Email')
                                ->email()
                                ->placeholder('mtsnmalang2adm@gmail.com'),
                        ])->columns(2),

                    Forms\Components\Section::make('WhatsApp')
                        ->description('Nomor WA Pengaduan dan Komite bersifat opsional — jika dikosongkan tidak akan ditampilkan di halaman publik.')
                        ->schema([
                            Forms\Components\TextInput::make('contact_wa_ptsp')
                                ->label('WA PTSP')
                                ->placeholder('6285163350000')
                                ->helperText('Format: 62xxx (tanpa + atau spasi)'),
                            Forms\Components\TextInput::make('contact_wa_pengaduan')
                                ->label('WA Pengaduan (opsional)')
                                ->placeholder('6281234567890')
                                ->helperText('Kosongkan jika tidak ingin ditampilkan'),
                            Forms\Components\TextInput::make('contact_wa_komite')
                                ->label('WA Komite (opsional)')
                                ->placeholder('6281234567890')
                                ->helperText('Kosongkan jika tidak ingin ditampilkan'),
                        ])->columns(3),

                    Forms\Components\Section::make('Lokasi & Jam Operasional')
                        ->schema([
                            Forms\Components\TextInput::make('contact_maps_url')
                                ->label('Link Google Maps')
                                ->url()
                                ->placeholder('https://maps.app.goo.gl/...')
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('contact_maps_embed')
                                ->label('URL Embed Google Maps (src iframe)')
                                ->rows(2)
                                ->placeholder('https://www.google.com/maps/embed?pb=...')
                                ->helperText('Salin URL dari bagian src="..." pada kode embed Google Maps')
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('contact_hours')
                                ->label('Jam Operasional')
                                ->rows(4)
                                ->placeholder("Senin – Kamis : 07.00 – 15.00 WIB\nJumat : 07.00 – 11.30 WIB\nSabtu – Minggu : Tutup")
                                ->helperText('Satu baris per jadwal')
                                ->columnSpanFull(),
                        ]),
                ]),

                // ── Tab 6: Kontak Ma'had ──────────────────────────────────────
                Forms\Components\Tabs\Tab::make("Kontak Ma'had")->schema([
                    Forms\Components\Section::make("Identitas Ma'had")
                        ->schema([
                            Forms\Components\TextInput::make('mahad_name')
                                ->label("Nama Ma'had")
                                ->placeholder("Ma'had Al-Ittihad MTsN 2 Kota Malang")
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('mahad_address')
                                ->label('Alamat Lengkap')
                                ->rows(2)
                                ->columnSpanFull(),
                        ]),

                    Forms\Components\Section::make('Informasi Kontak')
                        ->schema([
                            Forms\Components\TextInput::make('mahad_phone')
                                ->label('Nomor Telepon (Teks)')
                                ->placeholder('(0341) 711500'),
                            Forms\Components\TextInput::make('mahad_phone_link')
                                ->label('Nomor Telepon (Link tel:)')
                                ->placeholder('0341711500'),
                            Forms\Components\TextInput::make('mahad_email')
                                ->label('Email')
                                ->email(),
                        ])->columns(3),

                    Forms\Components\Section::make('WhatsApp')
                        ->description("Nomor WA Pengaduan dan Komite bersifat opsional — jika dikosongkan tidak akan ditampilkan.")
                        ->schema([
                            Forms\Components\TextInput::make('mahad_wa')
                                ->label("WA Ma'had")
                                ->placeholder('6281234567890')
                                ->helperText('Format: 62xxx (tanpa + atau spasi)'),
                            Forms\Components\TextInput::make('mahad_wa_pengaduan')
                                ->label('WA Pengaduan (opsional)')
                                ->placeholder('6281234567890'),
                            Forms\Components\TextInput::make('mahad_wa_komite')
                                ->label('WA Komite (opsional)')
                                ->placeholder('6281234567890'),
                        ])->columns(3),

                    Forms\Components\Section::make('Lokasi & Jam Operasional')
                        ->schema([
                            Forms\Components\TextInput::make('mahad_maps_url')
                                ->label('Link Google Maps')
                                ->url()
                                ->placeholder('https://maps.app.goo.gl/...')
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('mahad_maps_embed')
                                ->label('URL Embed Google Maps (src iframe)')
                                ->rows(2)
                                ->placeholder('https://www.google.com/maps/embed?pb=...')
                                ->helperText('Salin URL dari bagian src="..." pada kode embed Google Maps')
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('mahad_hours')
                                ->label('Jam Operasional')
                                ->rows(4)
                                ->placeholder("Senin – Jumat : 07.00 – 16.00 WIB\nSabtu : 07.00 – 12.00 WIB")
                                ->helperText('Satu baris per jadwal')
                                ->columnSpanFull(),
                        ]),

                    Forms\Components\Section::make("Sosial Media Ma'had")
                        ->description("Link sosial media khusus Ma'had — akan ditampilkan di halaman Kontak Ma'had. Kosongkan jika tidak ada.")
                        ->schema([
                            Forms\Components\TextInput::make('mahad_social_youtube')
                                ->label('YouTube')
                                ->url()
                                ->placeholder('https://www.youtube.com/channel/...')
                                ->prefixIcon('heroicon-o-play-circle'),
                            Forms\Components\TextInput::make('mahad_social_instagram')
                                ->label('Instagram')
                                ->url()
                                ->placeholder('https://www.instagram.com/...')
                                ->prefixIcon('heroicon-o-camera'),
                            Forms\Components\TextInput::make('mahad_social_facebook')
                                ->label('Facebook')
                                ->url()
                                ->placeholder('https://www.facebook.com/...')
                                ->prefixIcon('heroicon-o-user-group'),
                            Forms\Components\TextInput::make('mahad_social_tiktok')
                                ->label('TikTok')
                                ->url()
                                ->placeholder('https://www.tiktok.com/@...')
                                ->prefixIcon('heroicon-o-musical-note'),
                            Forms\Components\TextInput::make('mahad_wa_channel')
                                ->label("WhatsApp Channel")
                                ->url()
                                ->placeholder('https://whatsapp.com/channel/...')
                                ->helperText("Link channel WhatsApp resmi Ma'had.")
                                ->prefixIcon('heroicon-o-chat-bubble-left-ellipsis')
                                ->columnSpanFull(),
                        ])->columns(2),
                ]),

                // ── Tab 7: Email / SMTP ───────────────────────────────────────
                Forms\Components\Tabs\Tab::make('Email')->schema([

                    // Panduan
                    Forms\Components\Placeholder::make('email_guide')
                        ->label('')
                        ->content(new \Illuminate\Support\HtmlString('
                            <div class="rounded-xl border border-blue-200 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-800 p-4 text-sm space-y-3">
                                <p class="font-semibold text-blue-800 dark:text-blue-300 flex items-center gap-2">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Panduan Konfigurasi Email
                                </p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-blue-700 dark:text-blue-300">
                                    <div class="space-y-1">
                                        <p class="font-medium">📧 Gmail (Rekomendasi)</p>
                                        <ul class="text-xs space-y-0.5 text-blue-600 dark:text-blue-400 list-disc list-inside">
                                            <li>Host: <code class="bg-blue-100 dark:bg-blue-900 px-1 rounded">smtp.gmail.com</code></li>
                                            <li>Port: <code class="bg-blue-100 dark:bg-blue-900 px-1 rounded">587</code> (TLS)</li>
                                            <li>Username: alamat Gmail Anda</li>
                                            <li>Password: <strong>App Password</strong> (bukan password Gmail)</li>
                                            <li>Aktifkan 2FA Gmail → Buat App Password</li>
                                        </ul>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="font-medium">🏢 cPanel / Hosting</p>
                                        <ul class="text-xs space-y-0.5 text-blue-600 dark:text-blue-400 list-disc list-inside">
                                            <li>Host: <code class="bg-blue-100 dark:bg-blue-900 px-1 rounded">mail.domain.sch.id</code></li>
                                            <li>Port: <code class="bg-blue-100 dark:bg-blue-900 px-1 rounded">465</code> (SSL) atau <code class="bg-blue-100 dark:bg-blue-900 px-1 rounded">587</code> (TLS)</li>
                                            <li>Username: email@domain.sch.id</li>
                                            <li>Password: password email hosting</li>
                                        </ul>
                                    </div>
                                </div>
                                <p class="text-xs text-blue-600 dark:text-blue-400 border-t border-blue-200 dark:border-blue-700 pt-2">
                                    ⚠️ Simpan pengaturan terlebih dahulu sebelum melakukan tes koneksi atau tes kirim email.
                                </p>
                            </div>
                        '))
                        ->columnSpanFull(),

                    // Form SMTP
                    Forms\Components\Section::make('Konfigurasi SMTP')
                        ->description('Pengaturan email untuk pengiriman OTP, notifikasi, dan newsletter.')
                        ->schema([
                            Forms\Components\Select::make('mail_driver')
                                ->label('Mail Driver')
                                ->options([
                                    'smtp'     => 'SMTP',
                                    'sendmail' => 'Sendmail',
                                    'log'      => 'Log (Testing — tidak kirim email nyata)',
                                    'array'    => 'Array (Testing)',
                                ])
                                ->default('smtp')
                                ->required(),
                            Forms\Components\Select::make('mail_encryption')
                                ->label('Enkripsi')
                                ->options([
                                    'tls'  => 'TLS (Port 587 — Rekomendasi)',
                                    'ssl'  => 'SSL (Port 465)',
                                    ''     => 'Tidak Ada (Port 25)',
                                ])
                                ->default('tls'),
                            Forms\Components\TextInput::make('mail_host')
                                ->label('SMTP Host')
                                ->placeholder('smtp.gmail.com')
                                ->helperText('Gmail: smtp.gmail.com · cPanel: mail.domain.sch.id'),
                            Forms\Components\TextInput::make('mail_port')
                                ->label('SMTP Port')
                                ->placeholder('587')
                                ->helperText('TLS: 587 · SSL: 465 · Tanpa enkripsi: 25'),
                            Forms\Components\TextInput::make('mail_username')
                                ->label('Username / Email SMTP')
                                ->placeholder('email@gmail.com')
                                ->helperText('Biasanya sama dengan alamat email pengirim'),
                            Forms\Components\TextInput::make('mail_password')
                                ->label('Password / App Password')
                                ->password()
                                ->revealable()
                                ->placeholder(fn () => filled(Setting::get('mail_password')) ? '•••••••• (tersimpan — kosongkan jika tidak diubah)' : null)
                                ->helperText('Gmail: gunakan App Password (bukan password akun biasa)'),
                            Forms\Components\TextInput::make('mail_from_address')
                                ->label('From Address (Alamat Pengirim)')
                                ->email()
                                ->placeholder('noreply@mtsn2malang.sch.id'),
                            Forms\Components\TextInput::make('mail_from_name')
                                ->label('From Name (Nama Pengirim)')
                                ->placeholder('MTsN 2 Kota Malang'),
                        ])->columns(2),

                    // Tombol tes
                    Forms\Components\Section::make('Uji Koneksi & Kirim Email')
                        ->description('Simpan pengaturan terlebih dahulu, lalu gunakan tombol di bawah untuk menguji konfigurasi.')
                        ->schema([
                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('test_mail_connection')
                                    ->label('Tes Koneksi SMTP')
                                    ->icon('heroicon-o-signal')
                                    ->color('info')
                                    ->action('testMailConnection'),

                                Forms\Components\Actions\Action::make('test_send_email')
                                    ->label('Kirim Email Tes')
                                    ->icon('heroicon-o-paper-airplane')
                                    ->color('success')
                                    ->requiresConfirmation()
                                    ->modalHeading('Kirim Email Tes')
                                    ->modalDescription(fn () => 'Email tes akan dikirim ke alamat: ' . auth()->user()->email . '. Lanjutkan?')
                                    ->modalSubmitActionLabel('Ya, Kirim Sekarang')
                                    ->action('testSendEmail'),
                            ])->columnSpanFull(),
                        ]),
                ]),

                // ── Tab 8: Keamanan ───────────────────────────────────────────
                Forms\Components\Tabs\Tab::make('Keamanan')->schema([
                    Forms\Components\Toggle::make('login_otp_enabled')
                        ->label('Aktifkan Verifikasi OTP (2FA) saat Login')
                        ->helperText('Jika diaktifkan, setelah email & password benar, kode OTP 6 digit dikirim ke email pengguna dan wajib dimasukkan. PASTIKAN tab Email sudah dikonfigurasi dan "Kirim Email Tes" berhasil sebelum mengaktifkan — jika email gagal terkirim, tidak ada yang bisa login.')
                        ->onColor('success')
                        ->offColor('danger')
                        ->inline(false),
                ]),

            ])->columnSpanFull(),
        ])->statePath('data');
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ManageSettings save() getState error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            Notification::make()->title('Gagal menyimpan')->body('Error: ' . $e->getMessage())->danger()->send();
            return;
        }

        try {
            foreach ($data as $key => $value) {
                if (in_array($key, Setting::SECRET_KEYS, true)) {
                    // Kosong = pertahankan rahasia yang sudah tersimpan.
                    if (filled($value)) {
                        Setting::setSecret($key, $value);
                    }
                    continue;
                }

                Setting::set($key, $value);
            }

            foreach (Setting::SECRET_KEYS as $key) {
                $this->data[$key] = '';
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ManageSettings save() Setting::set error', ['error' => $e->getMessage()]);
            Notification::make()->title('Gagal menyimpan setting')->body($e->getMessage())->danger()->send();
            return;
        }

        // Terapkan pengaturan email ke config runtime agar langsung berlaku.
        \App\Support\MailSettings::apply();

        $this->logActivity('Pengaturan website diperbarui', ['keys' => array_keys($data)]);

        Notification::make()->title('Pengaturan berhasil disimpan!')->success()->send();
    }

    // ─── Log Monitoring ──────────────────────────────────────────────────────

    public function getLogContent(): array
    {
        $logPath = storage_path('logs/laravel.log');

        if (! File::exists($logPath)) {
            return ['lines' => [], 'size' => 0, 'exists' => false];
        }

        $size    = File::size($logPath);
        $content = $this->tailFile($logPath, max(500, $this->logLines * 3));
        $lines   = explode("\n", $content);

        // Kelompokkan baris menjadi entri log (dimulai dengan [YYYY-MM-DD)
        $entries = [];
        $current = '';
        foreach ($lines as $line) {
            if (preg_match('/^\[\d{4}-\d{2}-\d{2}/', $line) && $current !== '') {
                $entries[] = $current;
                $current   = $line;
            } else {
                $current .= ($current ? "\n" : '') . $line;
            }
        }
        if ($current) {
            $entries[] = $current;
        }

        // Filter berdasarkan level
        if ($this->logFilter !== 'all') {
            $level   = strtoupper($this->logFilter);
            $entries = array_filter($entries, fn($e) => str_contains(strtoupper($e), '.' . $level . ':') || str_contains(strtoupper($e), ' ' . $level . ' '));
        }

        // Ambil N entri terakhir
        $entries = array_slice(array_values($entries), -$this->logLines);

        return [
            'lines'  => array_reverse($entries), // terbaru di atas
            'size'   => $size,
            'exists' => true,
            'count'  => count($entries),
        ];
    }

    // ─── Test Email ──────────────────────────────────────────────────────────

    public function testMailConnection(): void
    {
        try {
            $data = $this->form->getRawState()['data'] ?? $this->data;
            $host = $data['mail_host'] ?? Setting::get('mail_host', '');
            $port = (int) ($data['mail_port'] ?? Setting::get('mail_port', 587));
            $enc  = $data['mail_encryption'] ?? Setting::get('mail_encryption', 'tls');

            if (empty($host)) {
                Notification::make()->title('SMTP Host belum diisi')->warning()->send();
                return;
            }

            $ctx     = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
            $timeout = 8;
            $prefix  = ($enc === 'ssl') ? 'ssl://' : '';
            $conn    = @stream_socket_client("{$prefix}{$host}:{$port}", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $ctx);

            if ($conn) {
                fclose($conn);
                Notification::make()
                    ->title('✅ Koneksi SMTP berhasil')
                    ->body("Terhubung ke {$host}:{$port} ({$enc})")
                    ->success()->send();
            } else {
                Notification::make()
                    ->title('❌ Koneksi SMTP gagal')
                    ->body("Tidak dapat terhubung ke {$host}:{$port}. Error: {$errstr}")
                    ->danger()->send();
            }
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    public function testSendEmail(): void
    {
        try {
            // Tes memakai pengaturan yang SUDAH DISIMPAN (sama seperti yang
            // dipakai OTP & newsletter), bukan isian form yang belum disimpan.
            \App\Support\MailSettings::apply();

            $to       = auth()->user()->email;
            $from     = config('mail.from.address');
            $fromName = config('mail.from.name');

            if (config('mail.default') === 'smtp' && (blank(config('mail.mailers.smtp.host')) || blank($from))) {
                Notification::make()
                    ->title('Konfigurasi belum lengkap')
                    ->body('Isi dan simpan Host serta From Address terlebih dahulu.')
                    ->warning()->send();
                return;
            }

            \Illuminate\Support\Facades\Mail::raw(
                "Ini adalah email tes dari sistem MTsN 2 Kota Malang.\n\nJika Anda menerima email ini, konfigurasi SMTP sudah benar.\n\nWaktu: " . now()->format('d M Y H:i:s'),
                function ($msg) use ($to, $fromName, $from) {
                    $msg->to($to)
                        ->subject('[Tes] Konfigurasi Email MTsN 2 Kota Malang')
                        ->from($from, $fromName);
                }
            );

            Notification::make()
                ->title('✅ Email tes berhasil dikirim')
                ->body("Email tes telah dikirim ke {$to}. Silakan cek inbox Anda.")
                ->success()->send();

        } catch (\Throwable $e) {
            Notification::make()
                ->title('❌ Gagal mengirim email tes')
                ->body($e->getMessage())
                ->danger()->send();
        }
    }

    // ─── Test AI ─────────────────────────────────────────────────────────────

    public function testAiConnection(): void
    {
        try {
            $data     = $this->data;
            $provider = $data['ai_provider']      ?? Setting::get('ai_provider', 'gemini');
            $geminiKey = filled($data['gemini_api_key'] ?? null) ? $data['gemini_api_key'] : Setting::getSecret('gemini_api_key', '');
            $openaiKey = filled($data['openai_api_key'] ?? null) ? $data['openai_api_key'] : Setting::getSecret('openai_api_key', '');

            if ($provider === 'gemini') {
                if (empty($geminiKey)) {
                    Notification::make()->title('Gemini API Key belum diisi')->warning()->send();
                    return;
                }
                $resp = \Illuminate\Support\Facades\Http::timeout(10)
                    ->withHeaders(['x-goog-api-key' => $geminiKey])
                    ->get('https://generativelanguage.googleapis.com/v1beta/models');
                if ($resp->successful()) {
                    $models = collect($resp->json('models', []))->pluck('name')->take(3)->implode(', ');
                    Notification::make()
                        ->title('✅ Gemini API terhubung')
                        ->body("Koneksi berhasil. Model tersedia: {$models}")
                        ->success()->send();
                } else {
                    $err = $resp->json('error.message') ?? $resp->body();
                    Notification::make()
                        ->title('❌ Gemini API gagal')
                        ->body("Error {$resp->status()}: {$err}")
                        ->danger()->send();
                }
            } else {
                if (empty($openaiKey)) {
                    Notification::make()->title('OpenAI API Key belum diisi')->warning()->send();
                    return;
                }
                $resp = \Illuminate\Support\Facades\Http::timeout(10)
                    ->withToken($openaiKey)
                    ->get('https://api.openai.com/v1/models');
                if ($resp->successful()) {
                    $models = collect($resp->json('data', []))->pluck('id')->filter(fn($m) => str_contains($m, 'gpt'))->take(3)->implode(', ');
                    Notification::make()
                        ->title('✅ OpenAI API terhubung')
                        ->body("Koneksi berhasil. Model GPT: {$models}")
                        ->success()->send();
                } else {
                    $err = $resp->json('error.message') ?? $resp->body();
                    Notification::make()
                        ->title('❌ OpenAI API gagal')
                        ->body("Error {$resp->status()}: {$err}")
                        ->danger()->send();
                }
            }
        } catch (\Throwable $e) {
            Notification::make()->title('Error koneksi AI')->body($e->getMessage())->danger()->send();
        }
    }

    public function clearLog(): void
    {
        $logPath = storage_path('logs/laravel.log');
        if (File::exists($logPath)) {
            File::put($logPath, '');
            $this->logActivity('Log aplikasi dibersihkan');
            Notification::make()->title('Log berhasil dibersihkan')->success()->send();
        }
    }

    public function refreshLog(): void
    {
        // Trigger re-render Livewire
        Notification::make()->title('Log diperbarui')->success()->send();
    }

    public function setLogFilter(string $filter): void
    {
        $this->logFilter = $filter;
    }

    public function setLogLines(int $lines): void
    {
        $this->logLines = $lines;
    }

    // ─── Audit Log ───────────────────────────────────────────────────────────

    public function getAuditLogs(): array
    {
        $empty = [
            'records'     => collect(),
            'total'       => 0,
            'perPage'     => 20,
            'currentPage' => 1,
            'lastPage'    => 1,
        ];

        try {
            if (! Schema::hasTable('activity_log')) {
                return $empty;
            }

            $perPage = 20;
            $query   = Activity::with('causer')->latest();

            if ($this->auditSearch) {
                $search = $this->auditSearch;
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhereHas('causer', fn($u) => $u->where('name', 'like', "%{$search}%"));
                });
            }

            if ($this->auditLogName) {
                $query->where('log_name', $this->auditLogName);
            }

            $total   = $query->count();
            $records = $query->skip(($this->auditPage - 1) * $perPage)->take($perPage)->get();

            return [
                'records'     => $records,
                'total'       => $total,
                'perPage'     => $perPage,
                'currentPage' => $this->auditPage,
                'lastPage'    => (int) ceil($total / $perPage) ?: 1,
            ];
        } catch (\Throwable $e) {
            Log::warning('getAuditLogs error: ' . $e->getMessage());
            return $empty;
        }
    }

    public function auditNextPage(): void
    {
        $data = $this->getAuditLogs();
        if ($this->auditPage < $data['lastPage']) {
            $this->auditPage++;
        }
    }

    public function auditPrevPage(): void
    {
        if ($this->auditPage > 1) {
            $this->auditPage--;
        }
    }

    public function getAuditLogNames(): array
    {
        try {
            if (! Schema::hasTable('activity_log')) {
                return [];
            }
            return Activity::distinct()->pluck('log_name')->filter()->values()->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    // ─── Helper ──────────────────────────────────────────────────────────────

    /**
     * Log aktivitas dengan aman — tidak crash jika tabel activity_log belum ada.
     */
    private function logActivity(string $description, array $properties = []): void
    {
        try {
            if (! Schema::hasTable('activity_log')) {
                return;
            }
            $logger = activity()->causedBy(auth()->user());
            if (! empty($properties)) {
                $logger->withProperties($properties);
            }
            $logger->log($description);
        } catch (\Throwable $e) {
            Log::warning('Activity log error: ' . $e->getMessage());
        }
    }

    private function tailFile(string $path, int $lines): string
    {
        $fp   = fopen($path, 'rb');
        $size = filesize($path);

        if ($size === 0) {
            fclose($fp);
            return '';
        }

        // Baca maksimal 2MB dari akhir file
        $readSize = min($size, 2 * 1024 * 1024);
        fseek($fp, -$readSize, SEEK_END);
        $content = fread($fp, $readSize);
        fclose($fp);

        return $content;
    }

    // ─── Backup & Restore ────────────────────────────────────────────────────

    public function getBackups()
    {
        return AppBackup::latest()->limit(30)->get();
    }

    public function hasRunningBackup(): bool
    {
        AppBackup::failStale();

        return AppBackup::where('status', 'running')->exists();
    }

    public function getRunningBackup(): ?AppBackup
    {
        return AppBackup::where('status', 'running')->latest()->first();
    }

    /**
     * Backup/restore/update saling eksklusif — tidak boleh dua berjalan
     * bersamaan (mis. backup manual ditekan di tengah restore sedang jalan
     * bisa menangkap database dalam kondisi separuh dipulihkan).
     *
     * hasRunningX() saja tidak cukup: dua klik cepat/dua tab browser bisa
     * lolos guard yang sama sebelum salah satunya sempat membuat record
     * 'running'. Cache::lock() membuat urutan cek+buat-record atomik lintas
     * request — request kedua akan gagal dapat lock dan ditolak dengan rapi,
     * bukan ikut membuat job kedua yang bentrok.
     */
    private function withExclusiveMaintenanceLock(callable $callback): void
    {
        $lock = \Illuminate\Support\Facades\Cache::lock('manage-settings:maintenance-op', 10);

        if (! $lock->get()) {
            Notification::make()->title('Ada proses lain yang baru saja dimulai, coba lagi sebentar')->warning()->send();
            return;
        }

        try {
            if ($this->hasRunningBackup() || $this->hasRunningRestore() || $this->hasRunningUpdate()) {
                Notification::make()->title('Tunggu proses backup/restore/update lain selesai')->warning()->send();
                return;
            }

            $callback();
        } finally {
            $lock->release();
        }
    }

    public function createBackup(): void
    {
        $this->withExclusiveMaintenanceLock(function () {
            $backup = AppBackup::create([
                'type' => 'manual',
                'status' => 'running',
                'app_version' => AppVersion::current(),
                'triggered_by' => auth()->id(),
                'started_at' => now(),
            ]);

            CreateBackupJob::dispatch($backup->id);

            $this->logActivity('Memulai backup aplikasi manual', ['backup_id' => $backup->id]);
            Notification::make()
                ->title('Backup dimulai')
                ->body('Proses berjalan di background. Halaman ini akan otomatis memperbarui status.')
                ->success()->send();
        });
    }

    public function downloadBackup(int $backupId)
    {
        $backup = AppBackup::findOrFail($backupId);

        if ($backup->status !== 'completed' || blank($backup->filename)) {
            Notification::make()->title('Backup ini belum siap diunduh')->warning()->send();
            return null;
        }

        $service = app(BackupService::class);
        $service->assertSafeFilename($backup->filename);
        $path = $service->absolutePathFor($backup->filename);

        if (! is_file($path)) {
            Notification::make()->title('File backup tidak ditemukan di server')->danger()->send();
            return null;
        }

        $this->logActivity('Mengunduh backup', ['backup_id' => $backup->id, 'filename' => $backup->filename]);

        // Diunduh lewat route biasa yang mengalirkan file dari disk — download
        // langsung dari aksi Livewire memuat seluruh file ke memori.
        $this->redirect(URL::temporarySignedRoute('backup.download', now()->addMinutes(5), ['backup' => $backup->id]));

        return null;
    }

    public function deleteBackup(int $backupId): void
    {
        $backup = AppBackup::findOrFail($backupId);

        if ($backup->status === 'running') {
            Notification::make()->title('Tidak bisa menghapus backup yang sedang berjalan')->warning()->send();
            return;
        }

        if (filled($backup->filename)) {
            app(BackupService::class)->delete($backup->filename);
        }

        $backup->delete();

        $this->logActivity('Menghapus backup', ['filename' => $backup->filename]);
        Notification::make()->title('Backup dihapus')->success()->send();
    }

    public function getRestores()
    {
        return AppRestore::latest()->limit(15)->get();
    }

    public function hasRunningRestore(): bool
    {
        return AppRestore::where('status', 'running')->exists();
    }

    public function getRunningRestore(): ?AppRestore
    {
        return AppRestore::where('status', 'running')->latest()->first();
    }

    /**
     * Batas ukuran upload efektif (byte): yang terkecil dari upload_max_filesize,
     * post_max_size, dan aturan upload sementara Livewire. Ditampilkan di form
     * upload supaya file yang kebesaran langsung ditolak sebelum diunggah.
     */
    public function getUploadLimitBytes(): int
    {
        $limit = \Symfony\Component\HttpFoundation\File\UploadedFile::getMaxFilesize();

        foreach ((array) config('livewire.temporary_file_upload.rules', []) as $rule) {
            if (is_string($rule) && preg_match('/^max:(\d+)$/', $rule, $m)) {
                $limit = min($limit, (int) $m[1] * 1024);
            }
        }

        return (int) $limit;
    }

    /** Dipanggil komponen upload bertahap setelah potongan terakhir diterima. */
    public function useChunkedRestoreUpload(string $token, string $name): void
    {
        if (! ChunkedUpload::completedPath($token)) {
            Notification::make()->title('File unggahan tidak ditemukan di server, unggah ulang')->danger()->send();
            return;
        }

        $this->restoreUploadToken = $token;
        $this->restoreUploadName = mb_substr($name, 0, 255);
    }

    /** Dipanggil saat memilih file baru — buang unggahan sebelumnya. */
    public function clearChunkedRestoreUpload(): void
    {
        ChunkedUpload::discard($this->restoreUploadToken);
        $this->restoreUploadToken = null;
        $this->restoreUploadName = null;
    }

    public function openRestoreConfirm(): void
    {
        if (! $this->restoreFile && ! ChunkedUpload::completedPath($this->restoreUploadToken)) {
            Notification::make()->title('Pilih file backup (.zip) terlebih dahulu')->warning()->send();
            return;
        }

        $this->restoreFromBackupId = null;
        $this->restoreConfirmText = '';
        $this->showRestoreConfirmModal = true;
    }

    /** Restore memakai backup yang sudah ada di daftar — tanpa unduh & unggah ulang. */
    public function confirmRestoreFromBackup(int $backupId): void
    {
        $backup = AppBackup::findOrFail($backupId);

        if ($backup->status !== 'completed' || blank($backup->filename)) {
            Notification::make()->title('Backup ini belum siap dipakai untuk restore')->warning()->send();
            return;
        }

        $this->restoreFromBackupId = $backup->id;
        $this->restoreConfirmText = '';
        $this->showRestoreConfirmModal = true;
    }

    public function cancelRestoreConfirm(): void
    {
        $this->showRestoreConfirmModal = false;
        $this->restoreConfirmText = '';
        $this->restoreFromBackupId = null;
    }

    /** Nama file yang akan direstore, untuk ditampilkan di modal konfirmasi. */
    public function getRestoreSourceLabel(): ?string
    {
        if ($this->restoreFromBackupId) {
            return AppBackup::find($this->restoreFromBackupId)?->filename;
        }

        return $this->restoreUploadToken ? $this->restoreUploadName : $this->restoreFile?->getClientOriginalName();
    }

    /**
     * Restore SANGAT merusak jika salah — wajib ketik "RESTORE" persis, dan
     * sistem otomatis membuat backup pengaman dari kondisi saat ini terlebih
     * dahulu (lihat RestoreService) sebelum menimpa apa pun.
     */
    public function runRestore(): void
    {
        if ($this->restoreConfirmText !== 'RESTORE') {
            Notification::make()->title('Ketik "RESTORE" persis untuk konfirmasi')->warning()->send();
            return;
        }

        $backupSource = $this->restoreFromBackupId ? AppBackup::find($this->restoreFromBackupId) : null;
        $chunkedPath = ChunkedUpload::completedPath($this->restoreUploadToken);

        if ($this->restoreFromBackupId && (! $backupSource || $backupSource->status !== 'completed')) {
            Notification::make()->title('Backup sumber tidak ditemukan')->danger()->send();
            return;
        }

        if (! $backupSource && ! $chunkedPath && ! $this->restoreFile) {
            Notification::make()->title('File backup tidak ditemukan, unggah ulang')->danger()->send();
            return;
        }

        $this->withExclusiveMaintenanceLock(function () use ($backupSource, $chunkedPath) {
            try {
                // File dihapus setelah restore — kecuali backup tersimpan yang dipakai ulang.
                $deleteSourceAfter = true;

                if ($backupSource) {
                    $service = app(BackupService::class);
                    $service->assertSafeFilename($backupSource->filename);
                    $originalName = $backupSource->filename;
                    $absolutePath = $service->absolutePathFor($backupSource->filename);
                    $deleteSourceAfter = false;

                    if (! is_file($absolutePath)) {
                        Notification::make()->title('File backup tidak ditemukan di server')->danger()->send();
                        return;
                    }
                } elseif ($chunkedPath) {
                    $originalName = $this->restoreUploadName ?? basename($chunkedPath);
                    $absolutePath = $chunkedPath;
                } else {
                    $originalName = $this->restoreFile->getClientOriginalName();

                    if (! str_ends_with(strtolower($originalName), '.zip')) {
                        Notification::make()->title('File harus berformat .zip')->danger()->send();
                        return;
                    }

                    // Path absolut diambil dari disk yang sama dengan tempat file
                    // disimpan (disk 'local' = storage/app/private), bukan ditebak.
                    $storedPath = $this->restoreFile->store('tmp-uploads', 'local');
                    $absolutePath = \Illuminate\Support\Facades\Storage::disk('local')->path($storedPath);
                }

                $restore = AppRestore::create([
                    'source_filename' => $originalName,
                    'status' => 'running',
                    'triggered_by' => auth()->id(),
                    'started_at' => now(),
                ]);

                RestoreBackupJob::dispatch($restore->id, $absolutePath, $deleteSourceAfter);

                // Metadata upload bertahap tidak diperlukan lagi; file .zip-nya
                // dihapus oleh job setelah restore.
                if ($chunkedPath) {
                    @unlink(ChunkedUpload::metaPath($this->restoreUploadToken));
                }

                $this->logActivity('Memulai restore aplikasi', ['source' => $originalName]);
                $this->reset([
                    'restoreFile', 'restoreUploadToken', 'restoreUploadName', 'restoreFromBackupId',
                    'restoreConfirmText', 'showRestoreConfirmModal',
                ]);

                Notification::make()
                    ->title('Restore dimulai')
                    ->body('Backup pengaman dari kondisi saat ini dibuat otomatis sebelum restore diterapkan.')
                    ->warning()->persistent()->send();
            } catch (\Throwable $e) {
                Notification::make()->title('Gagal memulai restore')->body($e->getMessage())->danger()->send();
            }
        });
    }

    // ─── Update Aplikasi ─────────────────────────────────────────────────────

    /**
     * Update dari panel dimatikan di Docker/Coolify (APP_SELF_UPDATE=false):
     * file container dibuat ulang setiap deploy.
     */
    private function selfUpdateAllowed(): bool
    {
        if (config('app.self_update')) {
            return true;
        }

        Notification::make()
            ->title('Update dari panel dinonaktifkan')
            ->body('Aplikasi berjalan di Docker/Coolify. Perbarui lewat GitHub lalu Redeploy di Coolify.')
            ->warning()->send();

        return false;
    }

    public function currentAppVersion(): string
    {
        return AppVersion::current();
    }

    public function hasGithubToken(): bool
    {
        return app(UpdateService::class)->hasToken();
    }

    public function saveGithubSettings(): void
    {
        if (! $this->selfUpdateAllowed()) {
            return;
        }

        $service = app(UpdateService::class);

        if (blank($this->updateGithubRepo) || ! str_contains($this->updateGithubRepo, '/')) {
            Notification::make()->title('Format repo harus "pemilik/nama-repo"')->warning()->send();
            return;
        }

        $service->setRepo($this->updateGithubRepo);

        if (filled($this->updateGithubTokenInput)) {
            $service->setToken($this->updateGithubTokenInput);
            $this->updateGithubTokenInput = '';
        }

        $this->logActivity('Memperbarui konfigurasi GitHub untuk update aplikasi');
        Notification::make()->title('Konfigurasi GitHub disimpan')->success()->send();
    }

    public function checkForUpdate(): void
    {
        if (! $this->selfUpdateAllowed()) {
            return;
        }

        try {
            $this->updateCheckResult = app(UpdateService::class)->checkForUpdate();
            $this->updateConfirm = false;

            if ($this->updateCheckResult['available']) {
                Notification::make()
                    ->title('Update tersedia: v' . $this->updateCheckResult['latest'])
                    ->success()->send();
            } else {
                Notification::make()->title('Aplikasi sudah versi terbaru')->success()->send();
            }
        } catch (\Throwable $e) {
            $this->updateCheckResult = null;
            Notification::make()->title('Gagal mengecek update')->body($e->getMessage())->danger()->send();
        }
    }

    public function hasRunningUpdate(): bool
    {
        return AppUpdate::where('status', 'running')->exists();
    }

    public function getUpdateHistory()
    {
        return AppUpdate::latest()->limit(15)->get();
    }

    public function applyUpdate(): void
    {
        if (! $this->selfUpdateAllowed()) {
            return;
        }

        if (! $this->updateConfirm) {
            Notification::make()->title('Centang konfirmasi sebelum menerapkan update')->warning()->send();
            return;
        }

        if (! $this->updateCheckResult || ! ($this->updateCheckResult['available'] ?? false)) {
            Notification::make()->title('Tidak ada update yang siap diterapkan')->warning()->send();
            return;
        }

        $this->withExclusiveMaintenanceLock(function () {
            $update = AppUpdate::create([
                'from_version' => AppVersion::current(),
                'to_version' => $this->updateCheckResult['latest'],
                'release_tag' => $this->updateCheckResult['tag'],
                'status' => 'running',
                'triggered_by' => auth()->id(),
                'started_at' => now(),
            ]);

            ApplyUpdateJob::dispatch($update->id, $this->updateCheckResult['tag']);

            $this->logActivity('Memulai update aplikasi', [
                'from' => $update->from_version,
                'to' => $update->to_version,
            ]);

            $this->updateConfirm = false;
            $this->updateCheckResult = null;

            Notification::make()
                ->title('Update dimulai')
                ->body('Backup otomatis dibuat lebih dulu. Jangan tutup atau reload halaman ini sampai selesai.')
                ->warning()->persistent()->send();
        });
    }

    // ─── Update Manual (Upload ZIP) ──────────────────────────────────────────

    public function openManualUpdateConfirm(): void
    {
        if (! $this->selfUpdateAllowed()) {
            return;
        }

        if (! $this->manualUpdateFile) {
            Notification::make()->title('Pilih file ZIP kode aplikasi terlebih dahulu')->warning()->send();
            return;
        }

        if (blank($this->manualUpdateVersion) || ! preg_match('/^\d+\.\d+\.\d+/', $this->manualUpdateVersion)) {
            Notification::make()->title('Isi nomor versi baru dengan format semver, mis. 1.1.0')->warning()->send();
            return;
        }

        if (version_compare($this->manualUpdateVersion, AppVersion::current(), '<=')) {
            Notification::make()
                ->title('Versi baru harus lebih tinggi dari versi saat ini')
                ->body('Versi saat ini: v' . AppVersion::current())
                ->warning()->send();
            return;
        }

        $this->manualUpdateConfirm = false;
        $this->showManualUpdateConfirmModal = true;
    }

    public function cancelManualUpdateConfirm(): void
    {
        $this->showManualUpdateConfirmModal = false;
        $this->manualUpdateConfirm = false;
    }

    public function runManualUpdate(): void
    {
        if (! $this->selfUpdateAllowed()) {
            return;
        }

        if (! $this->manualUpdateConfirm) {
            Notification::make()->title('Centang konfirmasi sebelum menerapkan update')->warning()->send();
            return;
        }

        if (! $this->manualUpdateFile) {
            Notification::make()->title('File ZIP tidak ditemukan, unggah ulang')->danger()->send();
            return;
        }

        $this->withExclusiveMaintenanceLock(function () {
            try {
                $originalName = $this->manualUpdateFile->getClientOriginalName();

                if (! str_ends_with(strtolower($originalName), '.zip')) {
                    Notification::make()->title('File harus berformat .zip')->danger()->send();
                    return;
                }

                // Path absolut diambil dari disk yang sama dengan tempat file
                // disimpan (disk 'local' = storage/app/private), bukan ditebak.
                $storedPath = $this->manualUpdateFile->store('tmp-uploads', 'local');
                $absolutePath = \Illuminate\Support\Facades\Storage::disk('local')->path($storedPath);
                $toVersion = trim($this->manualUpdateVersion);

                $update = AppUpdate::create([
                    'from_version' => AppVersion::current(),
                    'to_version' => $toVersion,
                    'release_tag' => null,
                    'status' => 'running',
                    'triggered_by' => auth()->id(),
                    'started_at' => now(),
                ]);

                ApplyManualUpdateJob::dispatch($update->id, $absolutePath, $toVersion);

                $this->logActivity('Memulai update aplikasi manual (upload ZIP)', [
                    'from' => $update->from_version,
                    'to' => $update->to_version,
                    'source_file' => $originalName,
                ]);

                $this->reset(['manualUpdateFile', 'manualUpdateVersion', 'manualUpdateConfirm', 'showManualUpdateConfirmModal']);

                Notification::make()
                    ->title('Update manual dimulai')
                    ->body('Backup otomatis dibuat lebih dulu. Jangan tutup atau reload halaman ini sampai selesai.')
                    ->warning()->persistent()->send();
            } catch (\Throwable $e) {
                Notification::make()->title('Gagal memulai update manual')->body($e->getMessage())->danger()->send();
            }
        });
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Simpan Pengaturan')
                ->action('save')
                ->color('primary'),
        ];
    }
}
