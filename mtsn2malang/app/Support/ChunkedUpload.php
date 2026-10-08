<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

/**
 * Upload file besar (backup .zip berukuran GB) secara bertahap: browser
 * memotong file per beberapa MB, server menyambungnya kembali di disk.
 * Satu permintaan tidak pernah melewati upload_max_filesize / post_max_size,
 * dan file tidak pernah dimuat utuh ke memori.
 *
 * Berkas per upload (token = 32 karakter hex buatan server):
 *   {token}.json  — metadata (nama, ukuran, potongan berikutnya, pemilik)
 *   {token}.part  — isi yang sedang disambung
 *   {token}.zip   — hasil akhir yang siap direstore
 */
class ChunkedUpload
{
    /** Ukuran potongan maksimum; browser memakai yang lebih kecil bila batas server rendah. */
    public const CHUNK_BYTES = 8 * 1024 * 1024;

    public static function directory(): string
    {
        $dir = storage_path('app/private/chunk-uploads');

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir;
    }

    public static function isValidToken(mixed $token): bool
    {
        return is_string($token) && preg_match('/^[a-f0-9]{32}$/', $token) === 1;
    }

    public static function newToken(): string
    {
        return bin2hex(random_bytes(16));
    }

    public static function metaPath(string $token): string
    {
        return self::directory() . '/' . $token . '.json';
    }

    public static function partPath(string $token): string
    {
        return self::directory() . '/' . $token . '.part';
    }

    /** Path file hasil akhir, atau null bila token tidak sah / upload belum selesai. */
    public static function completedPath(mixed $token): ?string
    {
        if (! self::isValidToken($token)) {
            return null;
        }

        $path = self::directory() . '/' . $token . '.zip';

        return is_file($path) ? $path : null;
    }

    /** @return array<string, mixed>|null */
    public static function readMeta(string $token): ?array
    {
        $path = self::metaPath($token);

        if (! is_file($path)) {
            return null;
        }

        $meta = json_decode((string) file_get_contents($path), true);

        return is_array($meta) ? $meta : null;
    }

    /** @param array<string, mixed> $meta */
    public static function writeMeta(string $token, array $meta): void
    {
        file_put_contents(self::metaPath($token), json_encode($meta), LOCK_EX);
    }

    public static function discard(mixed $token): void
    {
        if (! self::isValidToken($token)) {
            return;
        }

        foreach (['.json', '.part', '.zip'] as $ext) {
            @unlink(self::directory() . '/' . $token . $ext);
        }
    }

    /**
     * Hapus sisa upload yang tidak pernah dipakai (dibatalkan, tab ditutup,
     * dsb.). Dijalankan harian lewat scheduler (routes/console.php).
     *
     * @return int jumlah file yang dihapus
     */
    public static function cleanupStale(int $olderThanHours = 24): int
    {
        $cutoff = now()->subHours($olderThanHours)->getTimestamp();
        $deleted = 0;

        foreach (File::files(self::directory()) as $file) {
            if ($file->getMTime() < $cutoff && @unlink($file->getPathname())) {
                $deleted++;
            }
        }

        return $deleted;
    }
}
