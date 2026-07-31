<?php

namespace App\Jobs;

use App\Models\WordpressMigrationBatch;
use App\Services\WordpressMigrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class WordpressMigrationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Waktu maksimal job berjalan: 30 menit.
     * Cukup untuk migrasi ratusan post + download gambar.
     */
    public int $timeout = 1800;

    /**
     * Tidak perlu retry — migrasi harus dijalankan manual ulang jika gagal.
     */
    public int $tries = 1;

    public function __construct(
        public readonly string $mode,   // 'online' | 'offline'
        public readonly array  $data,
        public readonly int    $batchId,
    ) {}

    public function handle(WordpressMigrationService $service): void
    {
        $batch = WordpressMigrationBatch::find($this->batchId);

        if (! $batch) {
            Log::warning('WordpressMigrationJob: batch not found', ['batch_id' => $this->batchId]);
            return;
        }

        try {
            $batch->update(['status' => 'running']);

            if ($this->mode === 'online') {
                $result = $service->runMigration($this->data, $this->batchId);
            } else {
                $result = $service->runMigrationOffline($this->data, $this->batchId);
            }

            $batch->update([
                'status' => 'completed',
                'notes'  => $result['message'] ?? 'Selesai.',
            ]);

            Log::info('WordpressMigrationJob selesai', [
                'batch_id' => $this->batchId,
                'mode'     => $this->mode,
                'message'  => $result['message'] ?? '',
            ]);
        } catch (Throwable $e) {
            $batch->update([
                'status'      => 'failed',
                'notes'       => $e->getMessage(),
                'finished_at' => now(),
            ]);

            Log::error('WordpressMigrationJob gagal', [
                'batch_id' => $this->batchId,
                'error'    => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('WordpressMigrationJob::failed', [
            'batch_id' => $this->batchId,
            'error'    => $exception->getMessage(),
        ]);

        WordpressMigrationBatch::where('id', $this->batchId)
            ->where('status', 'running')
            ->update([
                'status'      => 'failed',
                'notes'       => 'Job gagal: ' . $exception->getMessage(),
                'finished_at' => now(),
            ]);
    }
}
