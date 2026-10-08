<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageSettings;
use App\Jobs\RestoreBackupJob;
use App\Models\AppBackup;
use App\Models\AppRestore;
use App\Models\User;
use App\Support\ChunkedUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Backup berukuran GB tidak bisa diunggah sekali kirim (batas upload server),
 * jadi restore memakai upload bertahap atau backup yang sudah ada di server.
 */
class ChunkedRestoreUploadTest extends TestCase
{
    protected function tearDown(): void
    {
        File::deleteDirectory(storage_path('app/private/chunk-uploads'));

        parent::tearDown();
    }

    private function admin(): User
    {
        return User::where('email', 'admin@mtsn2kotamalang.sch.id')->firstOrFail();
    }

    /** @return array<int, string> isi file dipotong per $size byte */
    private function chunks(string $content, int $size): array
    {
        return str_split($content, $size);
    }

    private function sendChunk(?string $token, int $index, int $total, string $content, string $piece, string $name = 'backup.zip')
    {
        return $this->actingAs($this->admin())->post(route('restore.chunk-upload'), array_filter([
            'token' => $token,
            'index' => $index,
            'total' => $total,
            'size' => strlen($content),
            'name' => $name,
            'chunk' => UploadedFile::fake()->createWithContent('chunk', $piece),
        ], fn ($v) => $v !== null), ['Accept' => 'application/json']);
    }

    public function test_chunks_are_joined_into_complete_zip(): void
    {
        $content = "PK\x03\x04" . random_bytes(5000);
        $pieces = $this->chunks($content, 2000);
        $token = null;

        foreach ($pieces as $index => $piece) {
            $response = $this->sendChunk($token, $index, count($pieces), $content, $piece)->assertOk();
            $token = $response->json('token');

            // Potongan yang dikirim ulang (retry) tidak boleh ditulis dua kali.
            if ($index === 1) {
                $this->sendChunk($token, 1, count($pieces), $content, $piece)->assertOk();
            }
        }

        $this->assertTrue($response->json('done'));
        $path = ChunkedUpload::completedPath($token);
        $this->assertNotNull($path);
        $this->assertSame($content, file_get_contents($path));
    }

    public function test_non_zip_and_out_of_order_chunks_are_rejected(): void
    {
        $content = str_repeat('x', 3000);
        $this->sendChunk(null, 0, 1, $content, $content)->assertStatus(422);
        $this->sendChunk(null, 0, 1, $content, $content, 'backup.sql')->assertStatus(422);

        $zip = "PK\x03\x04" . str_repeat('y', 3000);
        [$first] = $this->chunks($zip, 1000);
        $token = $this->sendChunk(null, 0, 4, $zip, $first)->assertOk()->json('token');
        $this->sendChunk($token, 2, 4, $zip, $first)->assertStatus(422);
        $this->sendChunk('bukan-token', 1, 4, $zip, $first)->assertStatus(422);
    }

    public function test_non_super_admin_cannot_upload_chunks(): void
    {
        $editor = $this->userByEmail('editor.chief@test.local');

        $this->actingAs($editor)->post(route('restore.chunk-upload'), [
            'index' => 0, 'total' => 1, 'size' => 4, 'name' => 'backup.zip',
            'chunk' => UploadedFile::fake()->createWithContent('chunk', "PK\x03\x04"),
        ], ['Accept' => 'application/json'])->assertForbidden();
    }

    public function test_completed_chunked_upload_can_be_restored(): void
    {
        Queue::fake();

        $content = "PK\x03\x04" . random_bytes(100);
        $token = $this->sendChunk(null, 0, 1, $content, $content)->assertOk()->json('token');

        Livewire::actingAs($this->admin())
            ->test(ManageSettings::class)
            ->set('activeMainTab', 'backup')
            ->call('useChunkedRestoreUpload', $token, 'backup-besar.zip')
            ->call('openRestoreConfirm')
            ->assertSee('backup-besar.zip')
            ->set('restoreConfirmText', 'RESTORE')
            ->call('runRestore');

        Queue::assertPushed(RestoreBackupJob::class, fn (RestoreBackupJob $job) => $job->deleteSourceAfter
            && $job->uploadedZipAbsolutePath === ChunkedUpload::completedPath($token));
        $this->assertDatabaseHas('app_restores', ['source_filename' => 'backup-besar.zip', 'status' => 'running']);
    }

    public function test_existing_backup_can_be_restored_without_upload_and_is_kept(): void
    {
        Queue::fake();

        $filename = 'backup-2026-10-08-000000-uji.zip';
        $path = storage_path('app/backups/' . $filename);
        File::ensureDirectoryExists(dirname($path));
        file_put_contents($path, "PK\x03\x04");

        $backup = AppBackup::create([
            'type' => 'manual', 'status' => 'completed', 'filename' => $filename,
            'size_bytes' => 4, 'app_version' => '1.0.0', 'started_at' => now(), 'finished_at' => now(),
        ]);

        try {
            Livewire::actingAs($this->admin())
                ->test(ManageSettings::class)
                ->call('confirmRestoreFromBackup', $backup->id)
                ->assertSet('showRestoreConfirmModal', true)
                ->set('restoreConfirmText', 'RESTORE')
                ->call('runRestore');

            Queue::assertPushed(RestoreBackupJob::class, fn (RestoreBackupJob $job) => ! $job->deleteSourceAfter
                && $job->uploadedZipAbsolutePath === $path);
            $this->assertSame($filename, AppRestore::latest('id')->value('source_filename'));
        } finally {
            @unlink($path);
        }
    }
}
