<?php

namespace App\Services;

use App\Models\AppBackup;
use App\Support\AppVersion;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * Backup aplikasi penuh: dump database + isi storage/app/public + .env,
 * dibungkus satu file ZIP dengan manifest.json untuk validasi saat restore.
 *
 * Backup TIDAK disimpan di storage/app/public (web-accessible) — selalu di
 * storage/app/backups, yang hanya bisa diakses lewat panel admin
 * (super_admin) via download terkontrol, bukan URL publik langsung.
 */
class BackupService
{
    public const DIR = 'backups';

    /**
     * Tabel operasional yang TIDAK ikut dibackup/direstore. Jika ikut, restore
     * akan menimpa riwayat backup/restore/update (termasuk record restore yang
     * sedang berjalan, sehingga statusnya hilang), menghidupkan lagi job
     * antrian lama, dan mengeluarkan semua admin dari sesinya.
     *
     * PENTING: tabel di sini tidak boleh punya foreign key ke tabel yang ikut
     * direstore — psql tidak bisa menghapus & membuat ulang tabel yang masih
     * dirujuk, sehingga restore akan gagal total.
     */
    public const EXCLUDED_TABLES = [
        'app_backups', 'app_restores', 'app_updates',
        'jobs', 'job_batches', 'failed_jobs',
        'cache', 'cache_locks', 'sessions',
        // Audit log tidak pernah ditimpa oleh restore (restore backup lama tidak
        // boleh menjadi cara menghapus jejak aktivitas). Isinya tetap diarsipkan
        // terpisah di activity_log.sql dalam ZIP — lihat dumpAuditLog().
        'activity_log',
    ];

    public function backupDisk(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return \Illuminate\Support\Facades\Storage::disk('local');
    }

    public function absoluteBackupDir(): string
    {
        $path = storage_path('app/' . self::DIR);

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return $path;
    }

    /**
     * @return array{filename: string, absolutePath: string, sizeBytes: int}
     */
    public function create(?callable $onProgress = null): array
    {
        $progress = $onProgress ?? function (string $_) {};

        $version = AppVersion::current();
        $filename = sprintf('backup-%s-%s.zip', $version, now()->format('Ymd-His'));
        $absolutePath = $this->absoluteBackupDir() . '/' . $filename;

        $tmpDir = storage_path('app/tmp-backup-' . uniqid());
        mkdir($tmpDir, 0755, true);

        try {
            $progress('Tahap 1/4: Membuat dump database...');
            $dbDumpPath = $tmpDir . '/database.sql';
            $this->dumpDatabase($dbDumpPath);
            $progress('Tahap 1/4: Dump database selesai (' . $this->formatBytes((int) filesize($dbDumpPath)) . ').');

            $progress('Tahap 2/4: Menulis manifest & arsip audit log...');
            $manifest = [
                'app_name' => config('app.name'),
                'app_version' => $version,
                'laravel_version' => app()->version(),
                'php_version' => PHP_VERSION,
                'db_connection' => config('database.default'),
                'created_at' => now()->toIso8601String(),
                'created_by' => 'MTsN 2 Kota Malang Admin Panel',
                'format_version' => 1,
            ];
            file_put_contents($tmpDir . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));

            $zip = new ZipArchive();
            if ($zip->open($absolutePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException("Tidak bisa membuat file ZIP di {$absolutePath}");
            }

            $zip->addFile($tmpDir . '/manifest.json', 'manifest.json');
            $zip->addFile($dbDumpPath, 'database.sql');

            $auditDumpPath = $tmpDir . '/activity_log.sql';
            if ($this->dumpAuditLog($auditDumpPath)) {
                $zip->addFile($auditDumpPath, 'activity_log.sql');
            }

            if (is_file(base_path('.env'))) {
                $zip->addFile(base_path('.env'), '.env');
            }

            $storagePublic = storage_path('app/public');
            if (is_dir($storagePublic)) {
                $progress('Tahap 3/4: Mendata file media...');
                $this->addDirectoryToZip($zip, $storagePublic, 'storage/app/public', $progress);
            }

            // Kompresi sebenarnya terjadi saat close() — bisa lama untuk media
            // yang besar, jadi laporkan persentasenya (tiap kelipatan 10%).
            $progress('Tahap 4/4: Mengompres & menulis file ZIP...');
            if (method_exists($zip, 'registerProgressCallback')) {
                $lastReported = -1;
                $zip->registerProgressCallback(0.01, function (float $rate) use ($progress, &$lastReported) {
                    $percent = (int) floor($rate * 10) * 10;
                    if ($percent > $lastReported) {
                        $lastReported = $percent;
                        $progress("Tahap 4/4: Mengompres & menulis file ZIP... {$percent}%");
                    }
                });
            }

            if (! $zip->close()) {
                throw new RuntimeException('Gagal menulis file ZIP (ruang disk penuh?): ' . $zip->getStatusString());
            }

            $progress('Selesai.');

            return [
                'filename' => $filename,
                'absolutePath' => $absolutePath,
                'sizeBytes' => filesize($absolutePath) ?: 0,
            ];
        } finally {
            File::deleteDirectory($tmpDir);
        }
    }

    /**
     * Catat backup yang sudah selesai dibuat (lihat create()) sebagai record
     * AppBackup — dipakai untuk backup pengaman otomatis (pre_restore/
     * pre_update) yang file-nya sudah ada tapi sebelumnya tidak pernah masuk
     * daftar di tab Backup & Restore karena hanya dibuat via create() tanpa
     * pernah dicatat ke database sama sekali.
     *
     * @param array{filename: string, absolutePath: string, sizeBytes: int} $createResult
     */
    public function recordCompleted(array $createResult, string $type): AppBackup
    {
        return AppBackup::create([
            'type' => $type,
            'status' => 'completed',
            'filename' => $createResult['filename'],
            'size_bytes' => $createResult['sizeBytes'],
            'app_version' => AppVersion::current(),
            'started_at' => now(),
            'finished_at' => now(),
        ]);
    }

    private function addDirectoryToZip(ZipArchive $zip, string $sourceDir, string $zipPrefix, ?callable $onProgress = null): void
    {
        $sourceDir = rtrim($sourceDir, '/');
        $iterator = fn () => new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $total = 0;
        foreach ($iterator() as $item) {
            if (! $item->isDir()) {
                $total++;
            }
        }

        $added = 0;
        foreach ($iterator() as $item) {
            $relativePath = $zipPrefix . '/' . ltrim(substr($item->getPathname(), strlen($sourceDir)), '/');

            if ($item->isDir()) {
                $zip->addEmptyDir($relativePath);
                continue;
            }

            $zip->addFile($item->getPathname(), $relativePath);
            $added++;

            if ($onProgress && ($added % 200 === 0 || $added === $total)) {
                $onProgress("Tahap 3/4: Mendata file media {$added}/{$total}...");
            }
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1) . ' ' . $units[$i];
    }

    private function dumpDatabase(string $outputPath): void
    {
        $connectionName = config('database.default');
        $config = config("database.connections.{$connectionName}");

        if (($config['driver'] ?? null) !== 'pgsql') {
            throw new RuntimeException(
                "Backup database hanya mendukung driver 'pgsql' saat ini, koneksi aktif: " . ($config['driver'] ?? 'unknown')
            );
        }

        // --clean --if-exists: dump berisi DROP ... IF EXISTS sebelum CREATE,
        // supaya restore idempoten (aman dijalankan menimpa skema yang sudah ada).
        $process = new Process([
            'pg_dump',
            '--host=' . $config['host'],
            '--port=' . $config['port'],
            '--username=' . $config['username'],
            '--no-password',
            '--format=plain',
            '--clean',
            '--if-exists',
            '--no-owner',
            '--no-privileges',
            // Sequence ID ikut dikecualikan: --exclude-table hanya melewatkan
            // tabelnya, sehingga DROP SEQUENCE saat restore akan gagal karena
            // sequence masih dipakai tabel yang tidak ikut direstore.
            ...array_merge(...array_map(fn (string $table) => [
                '--exclude-table=' . $table,
                '--exclude-table=' . $table . '_id_seq',
            ], self::EXCLUDED_TABLES)),
            '--file=' . $outputPath,
            $config['database'],
        ]);
        $process->setEnv(['PGPASSWORD' => (string) $config['password']]);
        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('pg_dump gagal: ' . $process->getErrorOutput());
        }

        if (! is_file($outputPath) || filesize($outputPath) === 0) {
            throw new RuntimeException('pg_dump menghasilkan file dump kosong.');
        }
    }

    /**
     * Arsip audit log (data saja) — disimpan di ZIP untuk keperluan
     * pemulihan manual, TIDAK dijalankan otomatis oleh RestoreService.
     */
    private function dumpAuditLog(string $outputPath): bool
    {
        $config = config('database.connections.' . config('database.default'));

        $process = new Process([
            'pg_dump',
            '--host=' . $config['host'],
            '--port=' . $config['port'],
            '--username=' . $config['username'],
            '--no-password',
            '--format=plain',
            '--data-only',
            '--table=activity_log',
            '--file=' . $outputPath,
            $config['database'],
        ]);
        $process->setEnv(['PGPASSWORD' => (string) $config['password']]);
        $process->setTimeout(600);
        $process->run();

        return $process->isSuccessful() && is_file($outputPath);
    }

    public function delete(string $filename): bool
    {
        $this->assertSafeFilename($filename);
        $path = $this->absoluteBackupDir() . '/' . $filename;

        return is_file($path) && unlink($path);
    }

    public function absolutePathFor(string $filename): string
    {
        $this->assertSafeFilename($filename);

        return $this->absoluteBackupDir() . '/' . $filename;
    }

    /**
     * Cegah path traversal — nama file backup harus benar-benar nama file
     * polos, bukan path relatif yang bisa keluar dari direktori backups.
     */
    public function assertSafeFilename(string $filename): void
    {
        if ($filename === '' || $filename !== basename($filename) || str_contains($filename, '..')) {
            throw new RuntimeException('Nama file backup tidak valid.');
        }
    }
}
