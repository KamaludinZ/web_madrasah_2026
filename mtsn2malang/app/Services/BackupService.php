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
            $progress('Membuat dump database...');
            $dbDumpPath = $tmpDir . '/database.sql';
            $this->dumpDatabase($dbDumpPath);

            $progress('Menulis manifest...');
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

            $progress('Mengemas file ke ZIP...');
            $zip = new ZipArchive();
            if ($zip->open($absolutePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException("Tidak bisa membuat file ZIP di {$absolutePath}");
            }

            $zip->addFile($tmpDir . '/manifest.json', 'manifest.json');
            $zip->addFile($dbDumpPath, 'database.sql');

            if (is_file(base_path('.env'))) {
                $zip->addFile(base_path('.env'), '.env');
            }

            $storagePublic = storage_path('app/public');
            if (is_dir($storagePublic)) {
                $this->addDirectoryToZip($zip, $storagePublic, 'storage/app/public');
            }

            $zip->close();

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

    private function addDirectoryToZip(ZipArchive $zip, string $sourceDir, string $zipPrefix): void
    {
        $sourceDir = rtrim($sourceDir, '/');
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = $zipPrefix . '/' . ltrim(substr($item->getPathname(), strlen($sourceDir)), '/');

            if ($item->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $zip->addFile($item->getPathname(), $relativePath);
            }
        }
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
