<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Pembersih HTML untuk semua konten rich-text yang ditampilkan mentah
 * ({!! !!}) di halaman publik — konten berita, halaman profil, popup, PPDB,
 * sambutan, banner, dsb.
 *
 * Disanitasi saat DITAMPILKAN (bukan saat disimpan) supaya konten lama yang
 * sudah terlanjur tersimpan di database juga ikut aman. Tanpa ini, pengguna
 * dengan role editor/moderator bisa menyisipkan <script>/onerror yang
 * berjalan di domain yang sama dengan panel admin (stored XSS).
 *
 * Memakai symfony/html-sanitizer yang sudah terpasang sebagai dependensi
 * filament/support (Filament v3.3+).
 */
class SafeHtml
{
    private static ?HtmlSanitizer $sanitizer = null;

    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        return self::sanitizer()->sanitize($html);
    }

    private static function sanitizer(): HtmlSanitizer
    {
        return self::$sanitizer ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                ->allowSafeElements()
                ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
                ->allowMediaSchemes(['http', 'https'])
                ->allowRelativeLinks()
                ->allowRelativeMedias()
                ->allowAttribute('class', '*')
                ->allowAttribute('style', '*')
                ->allowAttribute('target', ['a'])
                ->allowElement('iframe', ['src', 'width', 'height', 'title', 'allow', 'allowfullscreen', 'frameborder', 'loading', 'referrerpolicy'])
                ->allowElement('video', ['src', 'controls', 'width', 'height', 'poster', 'preload'])
                ->allowElement('audio', ['src', 'controls', 'preload'])
                ->allowElement('source', ['src', 'type'])
                ->forceAttribute('a', 'rel', 'noopener noreferrer')
                ->withAttributeSanitizer(new SafeHtmlAttributeSanitizer())
                ->withMaxInputLength(2_000_000)
        );
    }
}
