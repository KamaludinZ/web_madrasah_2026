<?php

namespace App\Support;

/**
 * Versi aplikasi dibaca dari file VERSION di root project — bukan dari
 * composer.json atau git tag, supaya tetap terbaca meski aplikasi di-deploy
 * dari ZIP release (tanpa direktori .git) seperti pola deploy manual yang
 * dipakai proyek ini (lihat DEPLOYMENT.md).
 */
class AppVersion
{
    private const FILE = 'VERSION';

    public static function current(): string
    {
        $path = base_path(self::FILE);

        if (! is_file($path)) {
            return '0.0.0';
        }

        $version = trim((string) file_get_contents($path));

        return $version !== '' ? $version : '0.0.0';
    }

    /**
     * Tulis versi baru ke file VERSION. Dipanggil setelah update aplikasi
     * berhasil diterapkan.
     */
    public static function write(string $version): void
    {
        file_put_contents(base_path(self::FILE), trim($version) . "\n");
    }
}
