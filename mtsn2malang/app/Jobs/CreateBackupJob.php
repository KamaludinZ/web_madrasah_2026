<?php

namespace App\Jobs;

use App\Models\AppBackup;
use App\Services\BackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;
    public int $tries = 1;

    public function __construct(public readonly int $backupId)
    {
    }

    public function handle(BackupService $service): void
    {
        $backup = AppBackup::find($this->backupId);

        if (! $backup) {
            Log::warning('CreateBackupJob: record tidak ditemukan', ['id' => $this->backupId]);
            return;
        }

        // Sudah ditandai gagal (mis. dianggap macet) — jangan dikerjakan lagi.
        if ($backup->status !== 'running') {
            return;
        }

        try {
            $backup->update(['progress' => 'Memulai backup...']);

            $result = $service->create(function (string $message) use ($backup) {
                // update() juga menyentuh updated_at → penanda backup masih hidup.
                $backup->update(['progress' => mb_substr($message, 0, 250)]);
            });

            $backup->update([
                'status' => 'completed',
                'progress' => 'Selesai.',
                'filename' => $result['filename'],
                'size_bytes' => $result['sizeBytes'],
                'finished_at' => now(),
            ]);
        } catch (Throwable $e) {
            $backup->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            Log::error('CreateBackupJob gagal', ['id' => $this->backupId, 'error' => $e->getMessage()]);

            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        AppBackup::where('id', $this->backupId)->where('status', 'running')->update([
            'status' => 'failed',
            'error' => $exception->getMessage(),
            'finished_at' => now(),
        ]);
    }
}
