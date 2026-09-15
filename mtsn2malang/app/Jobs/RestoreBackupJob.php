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

    public int $timeout = 900;
    public int $tries = 1;

    public function __construct(
        public readonly int $restoreId,
        public readonly string $uploadedZipAbsolutePath,
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

            $restore->update([
                'status' => 'completed',
                'safety_backup_filename' => $result['safetyBackupFilename'],
                'finished_at' => now(),
            ]);
        } catch (Throwable $e) {
            $restore->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            Log::error('RestoreBackupJob gagal', ['id' => $this->restoreId, 'error' => $e->getMessage()]);

            throw $e;
        } finally {
            if (is_file($this->uploadedZipAbsolutePath)) {
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
