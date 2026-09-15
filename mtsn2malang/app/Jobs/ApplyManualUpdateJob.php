<?php

namespace App\Jobs;

use App\Models\AppUpdate;
use App\Services\BackupService;
use App\Services\UpdateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ApplyManualUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1200;
    public int $tries = 1;

    public function __construct(
        public readonly int $updateId,
        public readonly string $uploadedZipAbsolutePath,
        public readonly string $toVersion,
    ) {
    }

    public function handle(UpdateService $updateService, BackupService $backupService): void
    {
        $update = AppUpdate::find($this->updateId);

        if (! $update) {
            Log::warning('ApplyManualUpdateJob: record tidak ditemukan', ['id' => $this->updateId]);
            return;
        }

        try {
            $result = $updateService->applyManualUpdate(
                $this->uploadedZipAbsolutePath,
                $this->toVersion,
                $backupService,
                fn (string $line) => $update->appendLog($line)
            );

            $update->update([
                'status' => 'completed',
                'to_version' => $result['toVersion'],
                'backup_filename' => $result['backupFilename'],
                'finished_at' => now(),
            ]);

            if (! $result['shellAvailable']) {
                $update->appendLog('SELESAI SEBAGIAN — composer install/migrate perlu dijalankan manual (lihat log di atas).');
            }
        } catch (Throwable $e) {
            $update->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            Log::error('ApplyManualUpdateJob gagal', ['id' => $this->updateId, 'error' => $e->getMessage()]);

            throw $e;
        } finally {
            if (is_file($this->uploadedZipAbsolutePath)) {
                @unlink($this->uploadedZipAbsolutePath);
            }
        }
    }

    public function failed(Throwable $exception): void
    {
        AppUpdate::where('id', $this->updateId)->where('status', 'running')->update([
            'status' => 'failed',
            'error' => $exception->getMessage(),
            'finished_at' => now(),
        ]);
    }
}
