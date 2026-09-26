<?php

namespace App\Support;

/**
 * Kode keamanan (CAPTCHA) berupa angka yang ditampilkan sebagai GAMBAR.
 *
 * Kode disimpan di session server dan hanya dikirim ke browser dalam bentuk
 * gambar yang sedikit diacak — sebelumnya angka ditulis sebagai teks biasa di
 * HTML sehingga bot bisa langsung membacanya. Setiap kode hanya berlaku sekali
 * dan kedaluwarsa setelah beberapa menit.
 */
class NumericCaptcha
{
    /** Formulir yang memakai CAPTCHA (dipakai juga untuk URL gambar). */
    public const CONTEXTS = ['login', 'komentar'];

    public const LENGTH = 5;

    private const TTL_SECONDS = 600;

    public static function generate(string $context): string
    {
        $code = '';
        for ($i = 0; $i < self::LENGTH; $i++) {
            $code .= (string) random_int(0, 9);
        }

        session()->put(self::key($context), [
            'code' => $code,
            'expires_at' => now()->addSeconds(self::TTL_SECONDS)->getTimestamp(),
        ]);

        return $code;
    }

    /**
     * Cek jawaban. Kode langsung hangus setelah dicek (benar maupun salah),
     * jadi setiap percobaan butuh gambar baru — mencegah tebak berulang.
     */
    public static function check(string $context, mixed $input): bool
    {
        $stored = session()->pull(self::key($context));

        if (! is_array($stored) || ! is_string($input) || ($stored['expires_at'] ?? 0) < now()->getTimestamp()) {
            return false;
        }

        $input = preg_replace('/\D/', '', $input);

        return $input !== '' && hash_equals((string) $stored['code'], $input);
    }

    /**
     * Gambar untuk kode baru (setiap gambar = kode baru).
     *
     * @return array{0: string, 1: string} [isi gambar, content-type]
     */
    public static function render(string $context): array
    {
        $code = self::generate($context);

        return function_exists('imagecreatetruecolor')
            ? [self::renderPng($code), 'image/png']
            : [self::renderSvg($code), 'image/svg+xml'];
    }

    private static function renderPng(string $code): string
    {
        $width = 170;
        $height = 56;
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 243, 246, 244));

        // Garis & titik pengganggu
        for ($i = 0; $i < 6; $i++) {
            $color = imagecolorallocate($image, random_int(150, 210), random_int(150, 210), random_int(150, 210));
            imagesetthickness($image, random_int(1, 2));
            imageline($image, random_int(0, $width), random_int(0, $height), random_int(0, $width), random_int(0, $height), $color);
        }
        for ($i = 0; $i < 250; $i++) {
            $color = imagecolorallocate($image, random_int(120, 220), random_int(120, 220), random_int(120, 220));
            imagesetpixel($image, random_int(0, $width - 1), random_int(0, $height - 1), $color);
        }

        // Setiap angka digambar kecil lalu diperbesar, diputar, dan digeser acak.
        $slot = (int) floor(($width - 20) / self::LENGTH);
        foreach (str_split($code) as $index => $digit) {
            $glyph = imagecreatetruecolor(12, 18);
            $transparent = imagecolorallocatealpha($glyph, 0, 0, 0, 127);
            imagealphablending($glyph, false);
            imagefill($glyph, 0, 0, $transparent);
            imagesavealpha($glyph, true);
            $ink = imagecolorallocate($glyph, random_int(10, 60), random_int(60, 110), random_int(20, 60));
            imagestring($glyph, 5, 2, 1, $digit, $ink);

            $scaled = imagecreatetruecolor(30, 44);
            imagealphablending($scaled, false);
            imagefill($scaled, 0, 0, imagecolorallocatealpha($scaled, 0, 0, 0, 127));
            imagesavealpha($scaled, true);
            imagecopyresampled($scaled, $glyph, 0, 0, 0, 0, 30, 44, 12, 18);

            $rotated = imagerotate($scaled, random_int(-18, 18), imagecolorallocatealpha($scaled, 0, 0, 0, 127));
            imagealphablending($image, true);
            imagecopy(
                $image,
                $rotated,
                10 + $index * $slot + random_int(-2, 4),
                random_int(2, 8),
                0,
                0,
                imagesx($rotated),
                imagesy($rotated)
            );
        }

        ob_start();
        imagepng($image);

        return (string) ob_get_clean();
    }

    /**
     * Cadangan untuk server tanpa ekstensi GD: angka digambar sebagai garis
     * gaya 7-segmen (bukan teks), dengan posisi & kemiringan acak.
     */
    private static function renderSvg(string $code): string
    {
        // Segmen: a(atas) b(kanan-atas) c(kanan-bawah) d(bawah) e(kiri-bawah) f(kiri-atas) g(tengah)
        $segments = [
            'a' => [0, 0, 1, 0], 'b' => [1, 0, 1, 1], 'c' => [1, 1, 1, 2], 'd' => [0, 2, 1, 2],
            'e' => [0, 1, 0, 2], 'f' => [0, 0, 0, 1], 'g' => [0, 1, 1, 1],
        ];
        $digits = [
            '0' => 'abcdef', '1' => 'bc', '2' => 'abdeg', '3' => 'abcdg', '4' => 'bcfg',
            '5' => 'acdfg', '6' => 'acdefg', '7' => 'abc', '8' => 'abcdefg', '9' => 'abcdfg',
        ];

        $shapes = '';
        for ($i = 0; $i < 5; $i++) {
            $shapes .= sprintf('<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="#b9c7bd" stroke-width="1.5"/>', random_int(0, 170), random_int(0, 56), random_int(0, 170), random_int(0, 56));
        }

        foreach (str_split($code) as $index => $digit) {
            $x = 16 + $index * 30 + random_int(-2, 3);
            $y = 10 + random_int(-2, 3);
            $skew = random_int(-12, 12);
            $shapes .= sprintf('<g transform="translate(%d %d) skewX(%d)" stroke="#1a5c30" stroke-width="3.5" stroke-linecap="round">', $x, $y, $skew);
            foreach (str_split($digits[$digit]) as $segment) {
                [$x1, $y1, $x2, $y2] = $segments[$segment];
                $shapes .= sprintf('<line x1="%d" y1="%d" x2="%d" y2="%d"/>', $x1 * 16, $y1 * 17, $x2 * 16, $y2 * 17);
            }
            $shapes .= '</g>';
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" width="170" height="56" viewBox="0 0 170 56"><rect width="170" height="56" fill="#f3f6f4"/>' . $shapes . '</svg>';
    }

    private static function key(string $context): string
    {
        return 'numeric_captcha.' . $context;
    }
}
