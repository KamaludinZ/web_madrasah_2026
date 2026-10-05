<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppBackup extends Model
{
    /**
     * Backup 'running' yang tidak memperbarui progres selama ini dianggap
     * macet (worker mati/di-restart di tengah proses). Harus lebih lama dari
     * langkah terpanjang tanpa laporan progres (pg_dump, maks. 600 detik).
     */
    public const STALE_AFTER_MINUTES = 15;

    /** Belum ada progres selama ini = job belum diambil worker. */
    public const WAITING_WORKER_AFTER_SECONDS = 45;

    protected $fillable = [
        'filename', 'type', 'status', 'progress', 'size_bytes', 'app_version',
        'error', 'triggered_by', 'started_at', 'finished_at',
    ];

    /**
     * Tandai gagal backup yang macet, supaya tidak selamanya "Berjalan" dan
     * tidak terus memblokir backup/restore/update berikutnya.
     */
    public static function failStale(): int
    {
        $stale = static::where('status', 'running')
            ->where('updated_at', '<', now()->subMinutes(self::STALE_AFTER_MINUTES))
            ->get();

        foreach ($stale as $backup) {
            $backup->update([
                'status' => 'failed',
                'error' => blank($backup->progress)
                    ? 'Backup tidak pernah dimulai: job tidak diambil worker antrian. Pastikan container "worker" berjalan di Coolify.'
                    : 'Backup berhenti tanpa kabar lebih dari ' . self::STALE_AFTER_MINUTES . " menit di tahap \"{$backup->progress}\". "
                        . 'Kemungkinan container worker mati/di-restart atau kehabisan memori.',
                'finished_at' => now(),
            ]);
        }

        return $stale->count();
    }

    /** Job belum mulai dikerjakan worker padahal sudah ditunggu cukup lama. */
    public function isWaitingForWorker(): bool
    {
        return $this->status === 'running'
            && blank($this->progress)
            && $this->created_at?->lt(now()->subSeconds(self::WAITING_WORKER_AFTER_SECONDS));
    }

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function triggeredBy()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function formattedSize(): string
    {
        if (! $this->size_bytes) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = (float) $this->size_bytes;
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1) . ' ' . $units[$i];
    }
}
