<?php

namespace App\Support;

use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Throwable;

/**
 * Memperkecil gambar upload agar website ringan dibuka di HP.
 *
 * Audit produksi (Nov 2026): foto berita tersimpan dalam resolusi kamera
 * (4569×3427, 1,5 MB), logo 2866×2832 (293 KB, tampil di setiap halaman) —
 * Beranda memuat 4,7 MB. Ukuran maksimum ditentukan per folder sesuai
 * ukuran tampil terbesar di website (×2 untuk layar HP yang tajam).
 */
final class ImageOptimizer
{
    public const QUALITY = 80;

    /** Batas sisi terpanjang (px) per folder; folder lain memakai DEFAULT_MAX. */
    private const MAX_BY_DIRECTORY = [
        'settings/branding'   => 512,   // logo & favicon (tampil ±48–120 px)
        'portal-apps'         => 256,
        'avatars'             => 400,
        'gtk'                 => 800,
        'school-leaders'      => 800,
        'testimonials'        => 600,
        'alumni-coordinators' => 800,
        'headmaster-history'  => 800,
        'achievements'        => 1200,
        'settings/headmaster' => 1000,
        'settings/welcome'    => 1000,
        'news/thumbnails'     => 1600,
    ];

    public const DEFAULT_MAX = 1920;

    /** File di bawah batas ini & resolusi wajar dibiarkan apa adanya. */
    public const SMALL_FILE_BYTES = 300 * 1024;

    public static function maxDimensionFor(string $relativePath): int
    {
        $path = ltrim(str_replace('\\', '/', $relativePath), '/');

        foreach (self::MAX_BY_DIRECTORY as $directory => $max) {
            if (str_starts_with($path, $directory . '/')) {
                return $max;
            }
        }

        return self::DEFAULT_MAX;
    }

    public static function manager(): ?ImageManager
    {
        foreach ([GdDriver::class => 'gd', ImagickDriver::class => 'imagick'] as $driver => $extension) {
            if (extension_loaded($extension)) {
                try {
                    return new ImageManager(new $driver());
                } catch (Throwable) {
                    // coba driver berikutnya
                }
            }
        }

        return null;
    }

    /**
     * Upload baru → WebP dengan sisi terpanjang ≤ $maxDimension.
     * Mengembalikan false bila tidak ada driver gambar (pemanggil menyimpan aslinya).
     */
    public static function toWebp(string $sourcePath, string $targetPath, int $maxDimension): bool
    {
        $manager = self::manager();
        if (! $manager) {
            return false;
        }

        $image = $manager->read($sourcePath);
        $image->scaleDown($maxDimension, $maxDimension);

        if (! is_dir(dirname($targetPath))) {
            mkdir(dirname($targetPath), 0755, true);
        }

        file_put_contents($targetPath, (string) $image->encode(new WebpEncoder(self::QUALITY)));

        return true;
    }

    /**
     * Perkecil file yang sudah ada DI TEMPAT (nama & format tetap, sehingga
     * alamat di database tidak berubah). Hanya ditulis bila hasilnya lebih kecil.
     *
     * @return array{before: int, after: int, changed: bool, reason: string}
     */
    public static function shrinkInPlace(string $fullPath, int $maxDimension, bool $dryRun = false): array
    {
        $before = (int) @filesize($fullPath);
        $info = @getimagesize($fullPath);
        $result = ['before' => $before, 'after' => $before, 'changed' => false, 'reason' => ''];

        if (! $info || ! in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            return ['reason' => 'bukan JPG/PNG/WEBP'] + $result;
        }

        $tooLarge = max($info[0], $info[1]) > $maxDimension;
        if (! $tooLarge && $before <= self::SMALL_FILE_BYTES) {
            return ['reason' => 'sudah optimal'] + $result;
        }

        $manager = self::manager();
        if (! $manager) {
            return ['reason' => 'driver gambar tidak tersedia'] + $result;
        }

        $image = $manager->read($fullPath);
        $image->scaleDown($maxDimension, $maxDimension);

        $encoded = (string) $image->encode(match ($info[2]) {
            IMAGETYPE_JPEG => new JpegEncoder(self::QUALITY),
            IMAGETYPE_PNG  => new PngEncoder(),
            default        => new WebpEncoder(self::QUALITY),
        });

        // Resolusi sudah wajar: tulis ulang hanya bila hemat ≥ 20% — mencegah
        // foto dikompres berulang-ulang (kualitas turun) setiap perintah dijalankan.
        $limit = $tooLarge ? $before : (int) ($before * 0.8);
        if (strlen($encoded) >= $limit) {
            return ['reason' => 'hasil tidak cukup lebih kecil'] + $result;
        }

        if (! $dryRun) {
            $temp = $fullPath . '.tmp-optimize';
            file_put_contents($temp, $encoded);
            rename($temp, $fullPath);
            clearstatcache(true, $fullPath);
        }

        return ['before' => $before, 'after' => strlen($encoded), 'changed' => true, 'reason' => $tooLarge ? "diperkecil ke ≤{$maxDimension}px" : 'dikompres ulang'];
    }
}
