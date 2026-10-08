<?php

namespace App\Jobs;

use App\Models\AppRestore;
use App\Services\BackupService;
use App\Services\RestoreService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class RestoreBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Backup berukuran GB: backup pengaman + ekstrak + impor database bisa lama.
    public int $timeout = 3600;
    public int $tries = 1;

    public function __construct(
        public readonly int $restoreId,
        public readonly string $uploadedZipAbsolutePath,
        // false = sumbernya backup tersimpan di server, jangan dihapus.
        public readonly bool $deleteSourceAfter = true,
    ) {
    }

    public function handle(RestoreService $service): void
    {
        $restore = AppRestore::find($this->restoreId);

        if (! $restore) {
            Log::warning('RestoreBackupJob: record tidak ditemukan', ['id' => $this->restoreId]);
            return;
        }

        try {
            $result = $service->restore(
                $this->uploadedZipAbsolutePath,
                fn (string $line) => $restore->appendLog($line)
            );

            // Backup lama (sebelum tabel operasional dikecualikan) ikut menimpa
            // tabel app_restores — tulis ulang record ini bila sudah hilang.
            $record = AppRestore::find($restore->id)
                ?? $restore->replicate()->forceFill(['id' => $restore->id]);

            $record->forceFill([
                'status' => 'completed',
                'safety_backup_filename' => $result['safetyBackupFilename'],
                'finished_at' => now(),
            ])->save();
        } catch (Throwable $e) {
            $restore->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            Log::error('RestoreBackupJob gagal', ['id' => $this->restoreId, 'error' => $e->getMessage()]);

            throw $e;
        } finally {
            if ($this->deleteSourceAfter && is_file($this->uploadedZipAbsolutePath)) {
                @unlink($this->uploadedZipAbsolutePath);
            }
        }
    }

    public function failed(Throwable $exception): void
    {
        AppRestore::where('id', $this->restoreId)->where('status', 'running')->update([
            'status' => 'failed',
            'error' => $exception->getMessage(),
            'finished_at' => now(),
        ]);
    }
}
