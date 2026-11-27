<?php

namespace App\Support;

/**
 * Deteksi spam yang biasa disusupkan ke situs WordPress sekolah yang diretas:
 * artikel berbahasa asing tidak wajar (mis. Rusia/Mandarin), tautan ke situs
 * judi/kasino/obat, dan tautan yang disembunyikan (display:none dsb.).
 *
 * Sengaja hanya menilai TAUTAN & aksara, bukan kata di teks — artikel sah
 * seperti "penyuluhan bahaya judi online" tidak ikut tertandai.
 */
final class ContentSpamDetector
{
    private const SPAM_LINK_PATTERN = '/(casino|kasino|vavada|1xbet|melbet|mostbet|pin-?up|joker123|sbobet|slot|gacor|maxwin|togel|toto(?:gel|macau)|judi|poker|bandarq|dominoqq|pragmatic|zeus|scatter|bet(?:88|365|win)|promo-?codes?|viagra|cialis|porn|xxx|escort|payday|crypto-?casino)/i';

    /** @return string[] alasan (kosong = bersih) */
    public static function reasons(?string $title, string $html): array
    {
        $reasons = [];
        $text = trim(html_entity_decode(strip_tags($html)) . ' ' . $title);

        // Aksara asing (Kiril, CJK, Thai, Hangul) mendominasi teks. Arab tidak
        // dihitung — wajar untuk konten madrasah.
        $letters = preg_match_all('/\p{L}/u', $text);
        $foreign = preg_match_all('/[\p{Cyrillic}\p{Han}\p{Hiragana}\p{Katakana}\p{Thai}\p{Hangul}]/u', $text);
        if ($letters > 40 && $foreign / max($letters, 1) > 0.2) {
            $reasons[] = 'teks didominasi aksara asing (' . round($foreign / $letters * 100) . '%)';
        }

        preg_match_all('/<a\b[^>]*href\s*=\s*["\']([^"\']+)["\']/i', $html, $links);
        foreach (array_unique($links[1]) as $href) {
            $host = strtolower((string) parse_url(html_entity_decode($href), PHP_URL_HOST));
            if ($host !== '' && preg_match(self::SPAM_LINK_PATTERN, $host)) {
                $reasons[] = 'tautan ke situs spam/judi: ' . $host;
            }
        }

        // Tautan di dalam elemen tersembunyi — modus SEO spam.
        if (preg_match('/<(\w+)\b[^>]*style\s*=\s*["\'][^"\']*(display\s*:\s*none|visibility\s*:\s*hidden|font-size\s*:\s*[01](?:px)?\b|left\s*:\s*-\d{3,}px|text-indent\s*:\s*-\d{3,}px)[^"\']*["\'][^>]*>(?:(?!<\/\1>).)*<a\b/is', $html)) {
            $reasons[] = 'tautan disembunyikan (display:none/teks tak terlihat)';
        }

        return array_values(array_unique($reasons));
    }
}
