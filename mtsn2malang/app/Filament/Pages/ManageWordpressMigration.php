<?php

namespace App\Filament\Pages;

use App\Jobs\WordpressMigrationJob;
use App\Models\Setting;
use App\Models\WordpressMigrationBatch;
use App\Services\WordpressMigrationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class ManageWordpressMigration extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-arrow-path';
    protected static ?string $navigationLabel = 'Migrasi WordPress';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?int    $navigationSort  = 5;
    protected static string  $view            = 'filament.pages.manage-wordpress-migration';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    // ─── State ───────────────────────────────────────────────────────────────

    /** Tab aktif: online | offline | rollback */
    public string $activeTab = 'online';

    /** Data form online */
    public ?array $onlineData = [];

    /** Data form offline */
    public ?array $offlineData = [];

    /** Preview stats (dipakai kedua tab) */
    public array $previewStats = [];

    /** Pesan hasil proses terakhir per tab */
    public ?string $onlineResult     = null;
    public ?string $onlineResultType = null;
    public ?string $offlineResult     = null;
    public ?string $offlineResultType = null;
    public ?string $rollbackResult     = null;
    public ?string $rollbackResultType = null;

    // ─── Mount ───────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $encPass = Setting::get('wp_migration_db_password', '');

        $this->onlineForm->fill([
            'wp_migration_host'                    => Setting::get('wp_migration_host', '127.0.0.1'),
            'wp_migration_port'                    => Setting::get('wp_migration_port', '3306'),
            'wp_migration_database'                => Setting::get('wp_migration_database', ''),
            'wp_migration_username'                => Setting::get('wp_migration_username', ''),
            'wp_migration_db_password'             => $encPass ? $this->decryptValue($encPass) : '',
            'wp_migration_prefix'                  => Setting::get('wp_migration_prefix', 'wp_'),
            'wp_migration_source_url'              => Setting::get('wp_migration_source_url', ''),
            'wp_migration_import_posts'            => (bool) Setting::get('wp_migration_import_posts', true),
            'wp_migration_import_categories'       => (bool) Setting::get('wp_migration_import_categories', true),
            'wp_migration_import_tags'             => (bool) Setting::get('wp_migration_import_tags', true),
            'wp_migration_import_images'           => (bool) Setting::get('wp_migration_import_images', true),
            'wp_migration_rewrite_content_images'  => (bool) Setting::get('wp_migration_rewrite_content_images', true),
            'wp_migration_dry_run'                 => (bool) Setting::get('wp_migration_dry_run', true),
        ]);

        $this->offlineForm->fill([
            'wp_migration_offline_host'      => Setting::get('wp_migration_offline_host', '127.0.0.1'),
            'wp_migration_offline_port'      => Setting::get('wp_migration_offline_port', '3306'),
            'wp_migration_offline_database'  => Setting::get('wp_migration_offline_database', ''),
            'wp_migration_offline_username'  => Setting::get('wp_migration_offline_username', ''),
            'wp_migration_offline_password'  => Setting::get('wp_migration_offline_password', ''),
            'wp_migration_offline_prefix'    => Setting::get('wp_migration_offline_prefix', 'wp_'),
            'wp_migration_offline_file'      => Setting::get('wp_migration_offline_file', null),
            'wp_migration_offline_file_path' => Setting::get('wp_migration_offline_file_path', ''),
            'wp_migration_source_url'        => Setting::get('wp_migration_source_url', ''),
            'wp_migration_import_images'     => (bool) Setting::get('wp_migration_import_images', true),
            'wp_migration_rewrite_content_images' => (bool) Setting::get('wp_migration_rewrite_content_images', true),
            'wp_migration_dry_run'           => (bool) Setting::get('wp_migration_dry_run', true),
        ]);
    }

    // ─── Forms ───────────────────────────────────────────────────────────────

    protected function getForms(): array
    {
        return ['onlineForm', 'offlineForm'];
    }

    public function onlineForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Koneksi Database WordPress')
                    ->schema([
                        Forms\Components\TextInput::make('wp_migration_host')
                            ->label('Host')->required()->default('127.0.0.1'),
                        Forms\Components\TextInput::make('wp_migration_port')
                            ->label('Port')->required()->default('3306'),
                        Forms\Components\TextInput::make('wp_migration_database')
                            ->label('Database Name')->required(),
                        Forms\Components\TextInput::make('wp_migration_username')
                            ->label('Username')->required(),
                        Forms\Components\TextInput::make('wp_migration_db_password')
                            ->label('Password')->password()->revealable(),
                        Forms\Components\TextInput::make('wp_migration_prefix')
                            ->label('Table Prefix')->default('wp_')->required(),
                        Forms\Components\TextInput::make('wp_migration_source_url')
                            ->label('Source URL WordPress')
                            ->placeholder('https://domain-wordpress.com')
                            ->url()
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Opsi Migrasi')
                    ->schema([
                        Forms\Components\Toggle::make('wp_migration_import_posts')
                            ->label('Migrasi Posts')->default(true),
                        Forms\Components\Toggle::make('wp_migration_import_categories')
                            ->label('Migrasi Kategori')->default(true),
                        Forms\Components\Toggle::make('wp_migration_import_tags')
                            ->label('Migrasi Tags')->default(true),
                        Forms\Components\Toggle::make('wp_migration_import_images')
                            ->label('Download Featured Image / Attachment')->default(true),
                        Forms\Components\Toggle::make('wp_migration_rewrite_content_images')
                            ->label('Rewrite URL gambar di konten')->default(true),
                        Forms\Components\Toggle::make('wp_migration_dry_run')
                            ->label('Dry Run (simulasi tanpa simpan data)')
                            ->helperText('Aktifkan untuk simulasi. Nonaktifkan untuk migrasi nyata.')
                            ->default(true)
                            ->onColor('warning')
                            ->offColor('success'),
                    ])->columns(2),
            ])
            ->statePath('onlineData');
    }

    public function offlineForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Sumber Data XML')
                    ->description('Upload file WXR dari WordPress atau isi path file di server.')
                    ->schema([
                        Forms\Components\FileUpload::make('wp_migration_offline_file')
                            ->label('Upload File WordPress XML (WXR)')
                            ->acceptedFileTypes(['text/xml', 'application/xml', 'application/octet-stream'])
                            ->directory('wordpress-imports')
                            ->disk('public')
                            ->visibility('public')
                            ->maxSize(512000)
                            ->downloadable()
                            ->preserveFilenames()
                            ->helperText('Upload file XML/WXR hingga 500MB.')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('wp_migration_offline_file_path')
                            ->label('Atau isi path file XML di server')
                            ->placeholder('/home/user/backup.WordPress.xml')
                            ->helperText('Jika upload gagal karena limit server, isi path file secara manual.')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Opsi Gambar & Konten')
                    ->schema([
                        Forms\Components\TextInput::make('wp_migration_source_url')
                            ->label('Source URL WordPress (untuk rewrite gambar)')
                            ->placeholder('https://domain-wordpress.com')
                            ->url()
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('wp_migration_import_images')
                            ->label('Download gambar dari URL sumber')->default(true),
                        Forms\Components\Toggle::make('wp_migration_rewrite_content_images')
                            ->label('Rewrite URL gambar di konten')->default(true),
                        Forms\Components\Toggle::make('wp_migration_dry_run')
                            ->label('Dry Run (simulasi tanpa simpan data)')
                            ->helperText('Aktifkan untuk simulasi. Nonaktifkan untuk migrasi nyata.')
                            ->default(true)
                            ->onColor('warning')
                            ->offColor('success'),
                    ])->columns(2),
            ])
            ->statePath('offlineData');
    }

    // ─── Tab switcher ─────────────────────────────────────────────────────────

    public function switchTab(string $tab): void
    {
        $this->activeTab  = $tab;
        $this->previewStats = [];
    }

    // ─── ONLINE actions ───────────────────────────────────────────────────────

    public function saveOnlineConfig(): void
    {
        $data = $this->onlineForm->getState();
        $this->persistOnlineConfig($data);
        Notification::make()->title('Konfigurasi online disimpan.')->success()->send();
    }

    public function testConnection(WordpressMigrationService $service): void
    {
        $data   = $this->onlineForm->getState();
        $result = $service->testConnection($data);
        $time   = now()->format('d-m-Y H:i:s');

        $this->onlineResultType = $result['ok'] ? 'success' : 'danger';
        $this->onlineResult     = "[$time] " . $result['message'];

        // Jika prefix terdeteksi otomatis, update field prefix di form
        if (! $result['ok'] && ! empty($result['detected_prefix'])) {
            $this->onlineData['wp_migration_prefix'] = $result['detected_prefix'];
            $this->onlineResult .= "\n→ Field prefix telah diupdate otomatis ke \"{$result['detected_prefix']}\". Klik Simpan Konfigurasi lalu Test Koneksi lagi.";
        }

        Notification::make()
            ->title($result['ok'] ? 'Koneksi Berhasil' : 'Koneksi Gagal')
            ->body($result['message'])
            ->{$result['ok'] ? 'success' : 'danger'}()
            ->send();
    }

    public function detectPrefix(WordpressMigrationService $service): void
    {
        $data   = $this->onlineForm->getState();
        $result = $service->detectPrefix($data);
        $time   = now()->format('d-m-Y H:i:s');

        $this->onlineResultType = $result['ok'] ? 'success' : 'warning';
        $this->onlineResult     = "[$time] " . $result['message'];

        // Auto-update field prefix jika berhasil terdeteksi
        if ($result['ok'] && ! empty($result['prefix'])) {
            $this->onlineData['wp_migration_prefix'] = $result['prefix'];
            $this->onlineResult .= "\n→ Field prefix telah diupdate otomatis ke \"{$result['prefix']}\". Klik Simpan Konfigurasi untuk menyimpan.";
        }

        // Tampilkan daftar tabel jika ada
        if (! empty($result['tables'])) {
            $tableList = implode(', ', array_slice($result['tables'], 0, 30));
            $this->onlineResult .= "\n→ Tabel di database: {$tableList}";
        }

        Notification::make()
            ->title($result['ok'] ? 'Prefix Terdeteksi' : 'Deteksi Prefix Gagal')
            ->body($result['message'])
            ->{$result['ok'] ? 'success' : 'warning'}()
            ->send();
    }

    public function previewOnline(WordpressMigrationService $service): void
    {
        try {
            $data               = $this->onlineForm->getState();
            $this->previewStats = $service->previewStats($data);
            $time               = now()->format('d-m-Y H:i:s');
            $this->onlineResultType = 'success';
            $this->onlineResult     = "[$time] Preview berhasil diperbarui.";
            Notification::make()->title('Preview Berhasil')->success()->send();
        } catch (\Throwable $e) {
            $this->onlineResultType = 'danger';
            $this->onlineResult     = '[' . now()->format('d-m-Y H:i:s') . '] Preview gagal: ' . $e->getMessage();
            $this->previewStats     = [];
            Notification::make()->title('Preview Gagal')->body($e->getMessage())->danger()->send();
        }
    }

    public function runOnlineMigration(WordpressMigrationService $service): void
    {
        $data     = $this->onlineForm->getState();
        $isDryRun = (bool) ($data['wp_migration_dry_run'] ?? true);
        $time     = now()->format('d-m-Y H:i:s');

        // Dry run: jalankan langsung (cepat, tidak download gambar)
        if ($isDryRun) {
            try {
                $result = $service->runMigration($data);
                $this->onlineResultType = 'success';
                $this->onlineResult     = "[$time] " . ($result['message'] ?? 'Dry run selesai.');
                Notification::make()->title('Dry Run Selesai')->body($result['message'] ?? '')->success()->send();
            } catch (\Throwable $e) {
                $this->onlineResultType = 'danger';
                $this->onlineResult     = "[$time] Dry run gagal: " . $e->getMessage();
                Notification::make()->title('Dry Run Gagal')->body($e->getMessage())->danger()->send();
            }
            return;
        }

        // Migrasi nyata: jalankan langsung (sync) — tidak butuh queue worker
        try {
            @set_time_limit(0);
            @ignore_user_abort(true);

            $batch = WordpressMigrationBatch::create([
                'mode'            => 'online',
                'status'          => 'running',
                'source_database' => $data['wp_migration_database'] ?? '',
                'source_prefix'   => trim($data['wp_migration_prefix'] ?? 'wp_'),
                'started_at'      => now(),
                'notes'           => 'Sedang diproses...',
            ]);

            WordpressMigrationJob::dispatchSync('online', $data, $batch->id);

            // Refresh status batch setelah selesai
            $batch->refresh();
            $this->onlineResultType = 'success';
            $this->onlineResult     = "[$time] Migrasi online selesai (Batch #{$batch->id}). " . ($batch->notes ?? '');

            Notification::make()
                ->title('Migrasi Online Selesai')
                ->body("Batch #{$batch->id} berhasil diproses. Cek tab Rollback untuk detail.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            $this->onlineResultType = 'danger';
            $this->onlineResult     = "[$time] Migrasi gagal: " . $e->getMessage();
            Notification::make()->title('Migrasi Gagal')->body($e->getMessage())->danger()->send();
        }
    }

    // ─── OFFLINE actions ──────────────────────────────────────────────────────

    public function saveOfflineConfig(): void
    {
        $data = $this->offlineForm->getState();
        $this->persistOfflineConfig($data);
        Notification::make()->title('Konfigurasi offline disimpan.')->success()->send();
    }

    public function validateXml(WordpressMigrationService $service): void
    {
        $data   = $this->offlineForm->getState();
        $result = $service->testOfflineBackup($data);
        $time   = now()->format('d-m-Y H:i:s');

        $this->offlineResultType = $result['ok'] ? 'success' : 'danger';
        $this->offlineResult     = "[$time] " . $result['message'];

        Notification::make()
            ->title($result['ok'] ? 'Validasi XML Berhasil' : 'Validasi XML Gagal')
            ->body($result['message'])
            ->{$result['ok'] ? 'success' : 'danger'}()
            ->send();
    }

    public function previewOffline(WordpressMigrationService $service): void
    {
        try {
            $data               = $this->offlineForm->getState();
            $this->previewStats = $service->previewStatsOffline($data);
            $time               = now()->format('d-m-Y H:i:s');
            $this->offlineResultType = 'success';
            $this->offlineResult     = "[$time] Preview offline berhasil.";
            Notification::make()->title('Preview Offline Berhasil')->success()->send();
        } catch (\Throwable $e) {
            $this->offlineResultType = 'danger';
            $this->offlineResult     = '[' . now()->format('d-m-Y H:i:s') . '] Preview gagal: ' . $e->getMessage();
            $this->previewStats      = [];
            Notification::make()->title('Preview Gagal')->body($e->getMessage())->danger()->send();
        }
    }

    public function runOfflineMigration(WordpressMigrationService $service): void
    {
        $data     = $this->offlineForm->getState();
        $isDryRun = (bool) ($data['wp_migration_dry_run'] ?? true);
        $time     = now()->format('d-m-Y H:i:s');

        // Dry run: jalankan langsung
        if ($isDryRun) {
            try {
                $result = $service->runMigrationOffline($data);
                $this->offlineResultType = 'success';
                $this->offlineResult     = "[$time] " . ($result['message'] ?? 'Dry run offline selesai.');
                Notification::make()->title('Dry Run Offline Selesai')->body($result['message'] ?? '')->success()->send();
            } catch (\Throwable $e) {
                $this->offlineResultType = 'danger';
                $this->offlineResult     = "[$time] Dry run gagal: " . $e->getMessage();
                Notification::make()->title('Dry Run Gagal')->body($e->getMessage())->danger()->send();
            }
            return;
        }

        // Migrasi nyata: jalankan langsung (sync) — tidak butuh queue worker
        try {
            @set_time_limit(0);
            @ignore_user_abort(true);

            $batch = WordpressMigrationBatch::create([
                'mode'        => 'offline',
                'status'      => 'running',
                'source_file' => 'XML/DB lokal',
                'started_at'  => now(),
                'notes'       => 'Sedang diproses...',
            ]);

            WordpressMigrationJob::dispatchSync('offline', $data, $batch->id);

            // Refresh status batch setelah selesai
            $batch->refresh();
            $this->offlineResultType = 'success';
            $this->offlineResult     = "[$time] Migrasi offline selesai (Batch #{$batch->id}). " . ($batch->notes ?? '');

            Notification::make()
                ->title('Migrasi Offline Selesai')
                ->body("Batch #{$batch->id} berhasil diproses. Cek tab Rollback untuk detail.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            $this->offlineResultType = 'danger';
            $this->offlineResult     = "[$time] Migrasi gagal: " . $e->getMessage();
            Notification::make()->title('Migrasi Gagal')->body($e->getMessage())->danger()->send();
        }
    }

    // ─── ROLLBACK actions ─────────────────────────────────────────────────────

    public function rollbackLastBatch(WordpressMigrationService $service): void
    {
        $result = $service->rollbackLastMigrationBatch();
        $time   = now()->format('d-m-Y H:i:s');

        $this->rollbackResultType = $result['ok'] ? 'success' : 'danger';
        $this->rollbackResult     = "[$time] " . $result['message'];

        Notification::make()
            ->title($result['ok'] ? 'Rollback Berhasil' : 'Rollback Gagal')
            ->body($result['message'])
            ->{$result['ok'] ? 'success' : 'danger'}()
            ->send();
    }

    // ─── Cancel & Retry batch ────────────────────────────────────────────────

    public function cancelBatch(int $batchId): void
    {
        $batch = WordpressMigrationBatch::find($batchId);

        if (! $batch) {
            Notification::make()->title('Batch tidak ditemukan')->danger()->send();
            return;
        }

        if (! in_array($batch->status, ['queued', 'running'])) {
            Notification::make()->title('Batch tidak bisa dibatalkan')->body('Hanya batch berstatus queued atau running yang bisa dibatalkan.')->warning()->send();
            return;
        }

        // Hapus dari tabel jobs jika masih queued
        try {
            DB::table('jobs')
                ->where('payload', 'like', '%"batchId":' . $batchId . '%')
                ->delete();
        } catch (\Throwable) {
            // Abaikan jika gagal hapus dari jobs
        }

        $batch->update([
            'status'      => 'cancelled',
            'notes'       => 'Dibatalkan secara manual oleh admin pada ' . now()->format('d M Y H:i:s'),
            'finished_at' => now(),
        ]);

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['batch_id' => $batchId])
            ->log("Batch migrasi WordPress #{$batchId} dibatalkan");

        Notification::make()
            ->title("Batch #{$batchId} berhasil dibatalkan")
            ->success()
            ->send();
    }

    public function retryBatch(int $batchId): void
    {
        $batch = WordpressMigrationBatch::find($batchId);

        if (! $batch) {
            Notification::make()->title('Batch tidak ditemukan')->danger()->send();
            return;
        }

        if (! in_array($batch->status, ['failed', 'cancelled'])) {
            Notification::make()->title('Batch tidak bisa di-retry')->body('Hanya batch berstatus failed atau cancelled yang bisa di-retry.')->warning()->send();
            return;
        }

        // Buat batch baru berdasarkan data batch lama
        $newBatch = WordpressMigrationBatch::create([
            'mode'            => $batch->mode,
            'status'          => 'running',
            'source_file'     => $batch->source_file,
            'source_database' => $batch->source_database,
            'source_prefix'   => $batch->source_prefix,
            'started_at'      => now(),
            'notes'           => "Retry dari Batch #{$batchId}. Sedang diproses...",
        ]);

        // Ambil data konfigurasi dari Setting untuk dispatch ulang
        $encPass = Setting::get('wp_migration_db_password', '');
        $data = [
            'wp_migration_host'                   => Setting::get('wp_migration_host', '127.0.0.1'),
            'wp_migration_port'                   => Setting::get('wp_migration_port', '3306'),
            'wp_migration_database'               => Setting::get('wp_migration_database', ''),
            'wp_migration_username'               => Setting::get('wp_migration_username', ''),
            'wp_migration_db_password'            => $encPass ? $this->decryptValue($encPass) : '',
            'wp_migration_prefix'                 => Setting::get('wp_migration_prefix', 'wp_'),
            'wp_migration_source_url'             => Setting::get('wp_migration_source_url', ''),
            'wp_migration_import_posts'           => (bool) Setting::get('wp_migration_import_posts', true),
            'wp_migration_import_categories'      => (bool) Setting::get('wp_migration_import_categories', true),
            'wp_migration_import_tags'            => (bool) Setting::get('wp_migration_import_tags', true),
            'wp_migration_import_images'          => (bool) Setting::get('wp_migration_import_images', true),
            'wp_migration_rewrite_content_images' => (bool) Setting::get('wp_migration_rewrite_content_images', true),
            'wp_migration_dry_run'                => false,
        ];

        @set_time_limit(0);
        @ignore_user_abort(true);
        WordpressMigrationJob::dispatchSync($newBatch->mode, $data, $newBatch->id);

        $newBatch->refresh();

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['original_batch_id' => $batchId, 'new_batch_id' => $newBatch->id])
            ->log("Retry batch migrasi WordPress #{$batchId} → Batch #{$newBatch->id}");

        Notification::make()
            ->title("Retry Batch #{$newBatch->id} Selesai")
            ->body($newBatch->notes ?? 'Proses selesai.')
            ->success()
            ->send();
    }

    public function forceCompleteBatch(int $batchId): void
    {
        $batch = WordpressMigrationBatch::find($batchId);
        if (! $batch) return;

        $batch->update([
            'status'      => 'failed',
            'notes'       => ($batch->notes ? $batch->notes . "\n" : '') . '[Force-failed oleh admin pada ' . now()->format('d M Y H:i:s') . ']',
            'finished_at' => now(),
        ]);

        Notification::make()->title("Batch #{$batchId} ditandai sebagai failed")->warning()->send();
    }

    // ─── Queue worker status ──────────────────────────────────────────────────

    public function isQueueWorkerRunning(): bool
    {
        // shell_exec sering dinonaktifkan di shared hosting — gunakan fallback aman
        try {
            if (function_exists('shell_exec') && ! in_array('shell_exec', array_map('trim', explode(',', ini_get('disable_functions'))))) {
                $output = @shell_exec('pgrep -f "queue:work" 2>/dev/null') ?? '';
                return trim($output) !== '';
            }
        } catch (\Throwable) {
            // fallback
        }

        // Fallback: cek apakah ada batch running yang baru diupdate (proxy indicator)
        return \App\Models\WordpressMigrationBatch::where('status', 'running')
            ->where('updated_at', '>=', now()->subMinutes(2))
            ->exists();
    }

    public function getQueueStats(): array
    {
        try {
            $pending  = DB::table('jobs')->count();
            $failed   = DB::table('failed_jobs')->count();
            $queued   = WordpressMigrationBatch::where('status', 'queued')->count();
            $running  = WordpressMigrationBatch::where('status', 'running')->count();
        } catch (\Throwable) {
            $pending = $failed = $queued = $running = 0;
        }

        return compact('pending', 'failed', 'queued', 'running');
    }

    // ─── Computed: riwayat batch ──────────────────────────────────────────────

    #[Computed]
    public function batchHistory(): \Illuminate\Database\Eloquent\Collection
    {
        return WordpressMigrationBatch::latest()->limit(20)->get();
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function persistOnlineConfig(array $data): void
    {
        $password = $data['wp_migration_db_password'] ?? '';
        Setting::set('wp_migration_host',                   $data['wp_migration_host'] ?? '');
        Setting::set('wp_migration_port',                   $data['wp_migration_port'] ?? '3306');
        Setting::set('wp_migration_database',               $data['wp_migration_database'] ?? '');
        Setting::set('wp_migration_username',               $data['wp_migration_username'] ?? '');
        Setting::set('wp_migration_db_password',            $password ? Crypt::encryptString($password) : '');
        Setting::set('wp_migration_prefix',                 $data['wp_migration_prefix'] ?? 'wp_');
        Setting::set('wp_migration_source_url',             $data['wp_migration_source_url'] ?? '');
        Setting::set('wp_migration_import_posts',           $data['wp_migration_import_posts'] ?? true);
        Setting::set('wp_migration_import_categories',      $data['wp_migration_import_categories'] ?? true);
        Setting::set('wp_migration_import_tags',            $data['wp_migration_import_tags'] ?? true);
        Setting::set('wp_migration_import_images',          $data['wp_migration_import_images'] ?? true);
        Setting::set('wp_migration_rewrite_content_images', $data['wp_migration_rewrite_content_images'] ?? true);
        Setting::set('wp_migration_dry_run',                $data['wp_migration_dry_run'] ?? true);
    }

    private function persistOfflineConfig(array $data): void
    {
        Setting::set('wp_migration_offline_host',      $data['wp_migration_offline_host'] ?? '');
        Setting::set('wp_migration_offline_port',      $data['wp_migration_offline_port'] ?? '3306');
        Setting::set('wp_migration_offline_database',  $data['wp_migration_offline_database'] ?? '');
        Setting::set('wp_migration_offline_username',  $data['wp_migration_offline_username'] ?? '');
        Setting::set('wp_migration_offline_password',  $data['wp_migration_offline_password'] ?? '');
        Setting::set('wp_migration_offline_prefix',    $data['wp_migration_offline_prefix'] ?? 'wp_');
        Setting::set('wp_migration_offline_file',      $data['wp_migration_offline_file'] ?? null);
        Setting::set('wp_migration_offline_file_path', $data['wp_migration_offline_file_path'] ?? '');
        Setting::set('wp_migration_source_url',        $data['wp_migration_source_url'] ?? '');
        Setting::set('wp_migration_import_images',     $data['wp_migration_import_images'] ?? true);
        Setting::set('wp_migration_rewrite_content_images', $data['wp_migration_rewrite_content_images'] ?? true);
        Setting::set('wp_migration_dry_run',           $data['wp_migration_dry_run'] ?? true);
    }

    private function decryptValue(string $value): string
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return '';
        }
    }

    protected function getFormActions(): array
    {
        return []; // Tombol dikelola di view per-tab
    }
}
