<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Saklar aktif/nonaktif Halaman Portal (Setting "portal_enabled").
 * Aktif (default): "/" menampilkan Portal. Nonaktif: "/" menampilkan Beranda
 * dan menu Portal di header disembunyikan — data Portal tidak dihapus.
 */
final class PortalSettings
{
    public const KEY = 'portal_enabled';

    public static function enabled(): bool
    {
        $value = Setting::get(self::KEY);

        // Belum pernah diatur = aktif. Nilai tersimpan sebagai string ("0"/"1").
        return $value === null || filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public static function setEnabled(bool $enabled): void
    {
        Setting::set(self::KEY, $enabled ? '1' : '0');

        // Isi sitemap bergantung pada status Portal (lihat SitemapController).
        Cache::forget('sitemap.xml');
    }

    /** Gambar latar penuh Halaman Portal (path di disk public). */
    public const BACKGROUND_IMAGE_KEY = 'portal_background_image';

    /** Transparansi gambar latar dalam persen (0 = tampil penuh, 100 = tak terlihat). */
    public const BACKGROUND_TRANSPARENCY_KEY = 'portal_background_transparency';

    public const DEFAULT_TRANSPARENCY = 80;

    /**
     * @return array{url: ?string, transparency: int, opacity: float}
     *         opacity = kepekatan gambar untuk CSS (1 - transparansi).
     */
    public static function background(): array
    {
        $path = Setting::get(self::BACKGROUND_IMAGE_KEY);
        $raw = Setting::get(self::BACKGROUND_TRANSPARENCY_KEY);
        $transparency = is_numeric($raw) ? (int) $raw : self::DEFAULT_TRANSPARENCY;
        $transparency = max(0, min(100, $transparency));

        return [
            'url'          => filled($path) ? asset('storage/' . $path) : null,
            'transparency' => $transparency,
            'opacity'      => round((100 - $transparency) / 100, 2),
        ];
    }
}
