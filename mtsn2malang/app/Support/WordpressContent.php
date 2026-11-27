<?php

namespace App\Support;

/**
 * Merapikan sisa "shortcode" WordPress di konten hasil migrasi sebelum
 * ditampilkan (lalu tetap disanitasi SafeHtml). Tanpa ini teks seperti
 * [gallery ids="39,38"] atau [embed]https://...[/embed] tampil mentah.
 *
 * - [embed]URL[/embed] / [video src=URL] → embed YouTube/Vimeo/Instagram
 *   (host yang diizinkan SafeHtml), selain itu menjadi tautan biasa.
 * - [caption ...]<img>teks[/caption] → <figure> + <figcaption>.
 * - [gallery ...] → dihapus (lampiran galeri WordPress tidak ikut migrasi).
 * - Shortcode page builder/plugin lain → tag shortcode dibuang, isinya tetap.
 */
final class WordpressContent
{
    private const BUILDER_SHORTCODES = 'vc_[a-z_]+|et_pb_[a-z_]+|fusion_[a-z_]+|su_[a-z_]+|av_[a-z_]+|cs_[a-z_]+|tie_[a-z_]+|wpforms|contact-form-7|metaslider|ngg_images|nggallery|slideshow|audio|playlist|wp_[a-z_]+';

    public static function normalize(?string $html): string
    {
        $html = (string) $html;

        if ($html === '' || ! str_contains($html, '[')) {
            return $html;
        }

        // [embed]URL[/embed]
        $html = preg_replace_callback(
            '#\[embed[^\]]*\]\s*(https?://[^\s\[\]<>"]+)\s*\[/embed\]#i',
            fn (array $m) => self::embed(html_entity_decode($m[1])),
            $html
        ) ?? $html;

        // [video src="URL"] / [video mp4="URL"]
        $html = preg_replace_callback(
            '#\[video\s[^\]]*?(?:src|mp4)\s*=\s*["\']?(https?://[^"\'\s\]]+)["\']?[^\]]*\](?:\s*\[/video\])?#i',
            fn (array $m) => self::embed(html_entity_decode($m[1])),
            $html
        ) ?? $html;

        // [caption id=".." align=".." width=".."]<img ...> Teks keterangan[/caption]
        $html = preg_replace_callback(
            '#\[caption[^\]]*\](.*?)\[/caption\]#is',
            function (array $m) {
                $inner = trim($m[1]);
                if (! preg_match('#^((?:<a\b[^>]*>\s*)?<img\b[^>]*>(?:\s*</a>)?)(.*)$#is', $inner, $parts)) {
                    return $inner;
                }
                $caption = trim($parts[2]);

                return '<figure>' . $parts[1] . ($caption !== '' ? '<figcaption>' . $caption . '</figcaption>' : '') . '</figure>';
            },
            $html
        ) ?? $html;

        // [gallery ids="..."] — datanya tidak ada di aplikasi baru.
        $html = preg_replace('#\[gallery\b[^\]]*\](?:\s*\[/gallery\])?#i', '', $html) ?? $html;

        // Shortcode plugin/page builder lain: buang tag, pertahankan isi.
        $html = preg_replace('#\[/?(?:' . self::BUILDER_SHORTCODES . ')\b[^\]]*\]#i', '', $html) ?? $html;

        // Paragraf yang kosong setelah shortcode dihapus.
        return preg_replace('#<p>(?:\s|&nbsp;)*</p>#i', '', $html) ?? $html;
    }

    private static function embed(string $url): string
    {
        $parts = parse_url($url);
        $host = strtolower(preg_replace('/^www\./', '', $parts['host'] ?? ''));
        $path = $parts['path'] ?? '';
        parse_str($parts['query'] ?? '', $query);

        $iframe = static fn (string $src, string $title) => '<iframe src="' . e($src) . '" title="' . e($title) . '" width="560" height="315" loading="lazy" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>';

        $youtubeId = match (true) {
            in_array($host, ['youtube.com', 'm.youtube.com'], true) && isset($query['v']) => $query['v'],
            in_array($host, ['youtube.com', 'm.youtube.com'], true) && preg_match('#^/(?:shorts|embed|live)/([\w-]{6,})#', $path, $m) === 1 => $m[1],
            $host === 'youtu.be' => ltrim($path, '/'),
            default => null,
        };
        if (is_string($youtubeId) && preg_match('/^[\w-]{6,20}$/', $youtubeId)) {
            return $iframe('https://www.youtube.com/embed/' . $youtubeId, 'Video YouTube');
        }

        if ($host === 'vimeo.com' && preg_match('#^/(\d+)#', $path, $m)) {
            return $iframe('https://player.vimeo.com/video/' . $m[1], 'Video Vimeo');
        }

        if ($host === 'instagram.com' && preg_match('#^/(p|reel|tv)/([\w-]+)#', $path, $m)) {
            return '<iframe src="https://www.instagram.com/' . $m[1] . '/' . $m[2] . '/embed" title="Postingan Instagram" width="400" height="560" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>';
        }

        if (! in_array($parts['scheme'] ?? '', ['http', 'https'], true)) {
            return '';
        }

        return '<a href="' . e($url) . '" target="_blank">' . e($url) . '</a>';
    }
}
