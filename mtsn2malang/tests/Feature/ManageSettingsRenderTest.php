<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageSettings;
use App\Jobs\ApplyManualUpdateJob;
use App\Jobs\CreateBackupJob;
use App\Models\AppBackup;
use App\Models\AppRestore;
use App\Models\AppUpdate;
use App\Models\User;
use App\Services\BackupService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class ManageSettingsRenderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Fake queue: test ini hanya mengecek record/status dibuat dengan benar,
        // bukan menjalankan job sungguhan (yang butuh pg_dump dsb).
        Queue::fake();
    }

    protected function tearDown(): void
    {
        // Upload sementara (restoreFile/manualUpdateFile) tidak dihapus otomatis
        // di sini karena job pembersihnya di-fake (tidak benar-benar jalan).
        \Illuminate\Support\Facades\File::deleteDirectory(storage_path('app/private/tmp-uploads'));

        parent::tearDown();
    }

    /**
     * Regresi nyata yang pernah terjadi: wire:model biasa (bukan .live) di
     * Livewire 3 itu deferred — nilainya baru sinkron ke server saat ada
     * request lain, bukan saat user mengetik/klik. Karena tombol konfirmasi
     * di modal restore/update di-disable berdasarkan properti itu LANGSUNG
     * di Blade (:disabled="$prop !== '...'"), tanpa .live tombolnya macet
     * disabled terus meski user sudah mengisi dengan benar. Test ini
     * memastikan modifier .live tidak pernah hilang lagi dari 3 input itu.
     */
    public function test_confirmation_inputs_use_live_wire_model_so_buttons_react_immediately(): void
    {
        $user = User::where('email', 'admin@mtsn2kotamalang.sch.id')->firstOrFail();

        // Input restoreConfirmText hanya dirender di dalam modal konfirmasi.
        $html = Livewire::actingAs($user)->test(ManageSettings::class)
            ->set('activeMainTab', 'backup')
            ->set('showRestoreConfirmModal', true)
            ->html();
        $this->assertStringContainsString('wire:model.live="restoreConfirmText"', $html);

        // Checkbox updateConfirm hanya dirender saat ada hasil cek update yang tersedia.
        $html = Livewire::actingAs($user)->test(ManageSettings::class)
            ->set('activeMainTab', 'update')
            ->set('updateCheckResult', ['current' => '1.0.0', 'latest' => '1.1.0', 'available' => true, 'tag' => 'v1.1.0', 'notes' => null, 'publishedAt' => null])
            ->html();
        $this->assertStringContainsString('wire:model.live="updateConfirm"', $html);

        // Checkbox manualUpdateConfirm hanya dirender di dalam modal konfirmasi update manual.
        $html = Livewire::actingAs($user)->test(ManageSettings::class)
            ->set('activeMainTab', 'update')
            ->set('showManualUpdateConfirmModal', true)
            ->html();
        $this->assertStringContainsString('wire:model.live="manualUpdateConfirm"', $html);
    }

    public function test_page_loads_and_tab_buttons_present(): void
    {
        $user = User::where('email', 'admin@mtsn2kotamalang.sch.id')->firstOrFail();
        $response = $this->actingAs($user)->get('/admin/manage-settings');

        $response->assertOk();
        $response->assertSee('Backup & Restore');
        $response->assertSee('Update Aplikasi');
    }

    public function test_backup_tab_renders(): void
    {
        $user = User::where('email', 'admin@mtsn2kotamalang.sch.id')->firstOrFail();

        Livewire::actingAs($user)
            ->test(ManageSettings::class)
            ->set('activeMainTab', 'backup')
            ->assertSee('Versi Aplikasi Saat Ini')
            ->assertSee('Buat Backup Sekarang')
            ->assertSee('Restore Aplikasi')
            ->assertSee('v1.0.0');
    }

    public function test_update_tab_renders(): void
    {
        $user = User::where('email', 'admin@mtsn2kotamalang.sch.id')->firstOrFail();

        Livewire::actingAs($user)
            ->test(ManageSettings::class)
            ->set('activeMainTab', 'update')
            ->assertSee('Cek Update')
            ->assertSee('Sumber Update (GitHub)')
            ->assertSee('v1.0.0');
    }

    public function test_create_backup_flow(): void
    {
        $user = User::where('email', 'admin@mtsn2kotamalang.sch.id')->firstOrFail();

        Livewire::actingAs($user)
            ->test(ManageSettings::class)
            ->set('activeMainTab', 'backup')
            ->call('createBackup')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('app_backups', 1);
        $this->assertDatabaseHas('app_backups', ['type' => 'manual', 'status' => 'running']);
        Queue::assertPushed(CreateBackupJob::class);
    }

    public function test_restore_requires_exact_confirmation_text(): void
    {
        $user = User::where('email', 'admin@mtsn2kotamalang.sch.id')->firstOrFail();

        Livewire::actingAs($user)
            ->test(ManageSettings::class)
            ->set('activeMainTab', 'backup')
            ->set('restoreConfirmText', 'restore') // huruf kecil, harus ditolak
            ->call('runRestore')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('app_restores', 0);
    }

    public function test_download_backup_streams_real_file(): void
    {
        $user = User::where('email', 'admin@mtsn2kotamalang.sch.id')->firstOrFail();

        $result = app(BackupService::class)->create();
        $backup = AppBackup::create([
            'type' => 'manual', 'status' => 'completed',
            'filename' => $result['filename'], 'size_bytes' => $result['sizeBytes'],
            'app_version' => '1.0.0', 'started_at' => now(), 'finished_at' => now(),
        ]);

        try {
            // Aksi Livewire hanya mengarahkan ke URL unduh bertanda tangan...
            $url = Livewire::actingAs($user)
                ->test(ManageSettings::class)
                ->call('downloadBackup', $backup->id)
                ->effects['redirect'] ?? null;

            $this->assertNotNull($url);

            // ...yang mengalirkan file dari disk (bukan lewat respons Livewire).
            $response = $this->actingAs($user)->get($url);
            $response->assertOk();
            $this->assertStringContainsString($result['filename'], (string) $response->headers->get('content-disposition'));

            // Tanpa tanda tangan atau tanpa login → ditolak.
            $this->get(route('backup.download', $backup))->assertForbidden();
            auth()->logout();
            $this->get($url)->assertRedirect();
        } finally {
            app(BackupService::class)->delete($result['filename']);
        }
    }

    public function test_apply_update_blocked_without_confirmation_checkbox(): void
    {
        $user = User::where('email', 'admin@mtsn2kotamalang.sch.id')->firstOrFail();

        Livewire::actingAs($user)
            ->test(ManageSettings::class)
            ->set('activeMainTab', 'update')
            ->set('updateCheckResult', ['current' => '1.0.0', 'latest' => '1.1.0', 'available' => true, 'tag' => 'v1.1.0', 'notes' => null, 'publishedAt' => null])
            ->set('updateConfirm', false)
            ->call('applyUpdate')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('app_updates', 0);
    }

    public function test_manual_update_tab_renders(): void
    {
        $user = User::where('email', 'admin@mtsn2kotamalang.sch.id')->firstOrFail();

        Livewire::actingAs($user)
            ->test(ManageSettings::class)
            ->set('activeMainTab', 'update')
            ->assertSee('Update Manual (Upload ZIP)')
            ->assertSee('Versi Baru');
    }

    public function test_manual_update_confirm_rejects_version_not_higher_than_current(): void
    {
        $user = User::where('email', 'admin@mtsn2kotamalang.sch.id')->firstOrFail();

        Livewire::actingAs($user)
            ->test(ManageSettings::class)
            ->set('activeMainTab', 'update')
            ->set('manualUpdateFile', UploadedFile::fake()->create('update.zip', 10))
            ->set('manualUpdateVersion', '1.0.0') // sama dengan versi sekarang, harus ditolak
            ->call('openManualUpdateConfirm')
            ->assertSet('showManualUpdateConfirmModal', false)
            ->assertHasNoErrors();
    }

    public function test_manual_update_blocked_without_confirmation_checkbox(): void
    {
        $user = User::where('email', 'admin@mtsn2kotamalang.sch.id')->firstOrFail();

        Livewire::actingAs($user)
            ->test(ManageSettings::class)
            ->set('activeMainTab', 'update')
            ->set('manualUpdateFile', UploadedFile::fake()->create('update.zip', 10))
            ->set('manualUpdateVersion', '1.1.0')
            ->set('manualUpdateConfirm', false)
            ->call('runManualUpdate')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('app_updates', 0);
        Queue::assertNotPushed(ApplyManualUpdateJob::class);
    }

    public function test_manual_update_dispatches_job_when_confirmed(): void
    {
        $user = User::where('email', 'admin@mtsn2kotamalang.sch.id')->firstOrFail();

        Livewire::actingAs($user)
            ->test(ManageSettings::class)
            ->set('activeMainTab', 'update')
            ->set('manualUpdateFile', UploadedFile::fake()->create('update.zip', 10))
            ->set('manualUpdateVersion', '1.1.0')
            ->set('manualUpdateConfirm', true)
            ->call('runManualUpdate')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('app_updates', 1);
        $this->assertDatabaseHas('app_updates', [
            'from_version' => '1.0.0',
            'to_version' => '1.1.0',
            'status' => 'running',
            'release_tag' => null,
        ]);
        Queue::assertPushed(ApplyManualUpdateJob::class);
    }
}
