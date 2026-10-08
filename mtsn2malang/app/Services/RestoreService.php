<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * Restore aplikasi penuh dari file backup ZIP yang dihasilkan BackupService.
 *
 * Urutan aman:
 *  1. Validasi struktur ZIP (manifest.json + database.sql wajib ada) SEBELUM
 *     mengubah apa pun.
 *  2. Buat backup otomatis dari kondisi SAAT INI (safety net) — kalau restore
 *     ternyata salah pilih file / gagal separuh jalan, admin masih bisa balik.
 *  3. Restore database lewat psql (dump sudah --clean --if-exists jadi aman
 *     ditimpakan ke skema yang sudah ada).
 *  4. Restore file storage/app/public.
 *  5. Bersihkan cache aplikasi.
 *
 * Tidak pernah menimpa .env dari file backup secara otomatis — kredensial
 * DB di server saat ini yang dipakai untuk restore, bukan yang ada di dalam
 * backup (mencegah restore ke database/environment yang salah).
 */
class RestoreService
{
    public function __construct(private readonly BackupService $backupService)
    {
    }

    /**
     * @return array{safetyBackupFilename: string}
     */
    public function restore(string $uploadedZipAbsolutePath, ?callable $onProgress = null): array
    {
        $progress = $onProgress ?? function (string $_) {};

        $progress('Tahap 1/5: Memvalidasi file backup...');
        $manifest = $this->validateAndReadManifest($uploadedZipAbsolutePath);

        $progress('Tahap 2/5: Membuat backup pengaman dari kondisi saat ini...');
        $safety = $this->backupService->create(fn (string $m) => $progress("  ↳ {$m}"));
        $this->backupService->recordCompleted($safety, 'pre_restore');

        $tmpDir = storage_path('app/tmp-restore-' . uniqid());
        mkdir($tmpDir, 0755, true);

        try {
            $progress('Tahap 3/5: Mengekstrak file backup...');
            $zip = new ZipArchive();
            if ($zip->open($uploadedZipAbsolutePath) !== true) {
                throw new RuntimeException('Gagal membuka file ZIP backup.');
            }
            $zip->extractTo($tmpDir);
            $zip->close();

            $dbDumpPath = $tmpDir . '/database.sql';
            if (! is_file($dbDumpPath)) {
                throw new RuntimeException('database.sql tidak ditemukan di dalam backup.');
            }

            $progress('Tahap 4/5: Memulihkan database...');
            $this->preserveAuditLog();
            try {
                $this->restoreDatabase($dbDumpPath);
            } finally {
                // Audit log tidak boleh hilang karena restore backup lama.
                $this->mergePreservedAuditLog();
            }

            $storageDir = $tmpDir . '/storage/app/public';
            if (is_dir($storageDir)) {
                $progress('Tahap 5/5: Memulihkan file media (storage)...');
                $this->restoreStorageFiles($storageDir);
            }

            $progress('Membersihkan cache aplikasi...');
            Artisan::call('optimize:clear');

            $progress('Restore selesai.');

            return ['safetyBackupFilename' => $safety['filename']];
        } finally {
            File::deleteDirectory($tmpDir);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validateAndReadManifest(string $zipPath): array
    {
        if (! is_file($zipPath)) {
            throw new RuntimeException('File backup tidak ditemukan.');
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('File bukan ZIP yang valid atau rusak.');
        }

        $manifestJson = $zip->getFromName('manifest.json');
        $hasDatabase = $zip->locateName('database.sql') !== false;
        $zip->close();

        if ($manifestJson === false) {
            throw new RuntimeException('File ini bukan backup aplikasi yang valid (manifest.json tidak ditemukan).');
        }

        if (! $hasDatabase) {
            throw new RuntimeException('File backup tidak berisi database.sql.');
        }

        $manifest = json_decode($manifestJson, true);

        if (! is_array($manifest) || ! isset($manifest['format_version'])) {
            throw new RuntimeException('manifest.json di dalam backup rusak atau tidak dikenali.');
        }

        if ((int) $manifest['format_version'] > 1) {
            throw new RuntimeException('Backup ini dibuat oleh versi aplikasi yang lebih baru dan mungkin tidak kompatibel.');
        }

        return $manifest;
    }

    private const AUDIT_KEEP_TABLE = 'activity_log_keep_restore';

    /**
     * Salin audit log saat ini ke tabel sementara sebelum restore. Backup lama
     * (dibuat sebelum activity_log dikecualikan dari dump) masih berisi DROP +
     * CREATE activity_log, yang akan menghapus jejak aktivitas terbaru.
     */
    private function preserveAuditLog(): void
    {
        if (! Schema::hasTable('activity_log')) {
            return;
        }

        DB::statement('DROP TABLE IF EXISTS ' . self::AUDIT_KEEP_TABLE);
        DB::statement('CREATE TABLE ' . self::AUDIT_KEEP_TABLE . ' AS TABLE activity_log');
    }

    private function mergePreservedAuditLog(): void
    {
        if (! Schema::hasTable(self::AUDIT_KEEP_TABLE)) {
            return;
        }

        if (Schema::hasTable('activity_log')) {
            $columns = array_values(array_intersect(
                Schema::getColumnListing('activity_log'),
                Schema::getColumnListing(self::AUDIT_KEEP_TABLE)
            ));
            $list = implode(', ', array_map(fn (string $column) => '"' . $column . '"', $columns));

            DB::statement(
                "INSERT INTO activity_log ({$list}) SELECT {$list} FROM " . self::AUDIT_KEEP_TABLE . ' k '
                . 'WHERE NOT EXISTS (SELECT 1 FROM activity_log a WHERE a.id = k.id)'
            );
            DB::statement("SELECT setval(pg_get_serial_sequence('activity_log', 'id'), (SELECT COALESCE(MAX(id), 0) + 1 FROM activity_log), false)");
        }

        DB::statement('DROP TABLE IF EXISTS ' . self::AUDIT_KEEP_TABLE);
    }

    private function restoreDatabase(string $dumpPath): void
    {
        $connectionName = config('database.default');
        $config = config("database.connections.{$connectionName}");

        if (($config['driver'] ?? null) !== 'pgsql') {
            throw new RuntimeException(
                "Restore database hanya mendukung driver 'pgsql' saat ini, koneksi aktif: " . ($config['driver'] ?? 'unknown')
            );
        }

        $process = new Process([
            'psql',
            '--host=' . $config['host'],
            '--port=' . $config['port'],
            '--username=' . $config['username'],
            '--no-password',
            '--single-transaction',
            '--set=ON_ERROR_STOP=1',
            '--file=' . $dumpPath,
            $config['database'],
        ]);
        $process->setEnv(['PGPASSWORD' => (string) $config['password']]);
        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('Restore database gagal: ' . $process->getErrorOutput());
        }
    }

    private function restoreStorageFiles(string $sourceDir): void
    {
        $destDir = storage_path('app/public');

        if (! is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relative = ltrim(substr($item->getPathname(), strlen(rtrim($sourceDir, '/'))), '/');
            $target = $destDir . '/' . $relative;

            if ($item->isDir()) {
                if (! is_dir($target)) {
                    mkdir($target, 0755, true);
                }
            } else {
                copy($item->getPathname(), $target);
            }
        }
    }
}
