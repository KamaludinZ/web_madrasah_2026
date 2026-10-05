<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageSettings;
use App\Jobs\CreateBackupJob;
use App\Models\AppBackup;
use App\Models\User;
use App\Services\BackupService;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class BackupProgressTest extends TestCase
{
    private function admin(): User
    {
        return User::where('email', 'admin@mtsn2kotamalang.sch.id')->firstOrFail();
    }

    private function runningBackup(array $attributes = []): AppBackup
    {
        return AppBackup::create(array_merge([
            'type' => 'manual',
            'status' => 'running',
            'started_at' => now(),
        ], $attributes));
    }

    public function test_job_records_each_progress_step(): void
    {
        $backup = $this->runningBackup();
        $seen = [];

        $service = $this->mock(BackupService::class);
        $service->shouldReceive('create')->once()->andReturnUsing(function (callable $progress) use ($backup, &$seen) {
            $progress('Tahap 1/4: Membuat dump database...');
            $seen[] = $backup->fresh()->progress;

            return ['filename' => 'backup-test.zip', 'absolutePath' => '/tmp/backup-test.zip', 'sizeBytes' => 10];
        });

        (new CreateBackupJob($backup->id))->handle($service);

        $this->assertSame(['Tahap 1/4: Membuat dump database...'], $seen);
        $this->assertSame('completed', $backup->fresh()->status);
    }

    public function test_stale_running_backup_is_marked_failed_and_unblocks_new_backup(): void
    {
        Queue::fake();

        $stale = $this->runningBackup(['progress' => 'Tahap 4/4: Mengompres & menulis file ZIP... 40%']);
        AppBackup::whereKey($stale->id)->update(['updated_at' => now()->subMinutes(AppBackup::STALE_AFTER_MINUTES + 1)]);

        Livewire::actingAs($this->admin())->test(ManageSettings::class)
            ->set('activeMainTab', 'backup')
            ->call('createBackup');

        $stale->refresh();
        $this->assertSame('failed', $stale->status);
        $this->assertStringContainsString('Mengompres', $stale->error);
        Queue::assertPushed(CreateBackupJob::class);
    }

    public function test_never_started_backup_reports_worker_problem(): void
    {
        $backup = $this->runningBackup();
        AppBackup::whereKey($backup->id)->update([
            'created_at' => now()->subMinutes(AppBackup::STALE_AFTER_MINUTES + 1),
            'updated_at' => now()->subMinutes(AppBackup::STALE_AFTER_MINUTES + 1),
        ]);

        AppBackup::failStale();

        $this->assertStringContainsString('worker', $backup->fresh()->error);
    }

    public function test_backup_tab_shows_progress_and_waiting_for_worker_warning(): void
    {
        $backup = $this->runningBackup(['progress' => 'Tahap 3/4: Mendata file media 200/450...']);

        Livewire::actingAs($this->admin())->test(ManageSettings::class)
            ->set('activeMainTab', 'backup')
            ->assertSee('Tahap 3/4: Mendata file media 200/450...');

        AppBackup::whereKey($backup->id)->update([
            'progress' => null,
            'created_at' => now()->subMinute(),
        ]);

        Livewire::actingAs($this->admin())->test(ManageSettings::class)
            ->set('activeMainTab', 'backup')
            ->assertSee('belum diambil worker');
    }
}
