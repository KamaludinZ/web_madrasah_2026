<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

/**
 * Pelindung formulir publik (pengaduan, whistleblowing, saran):
 *
 * - honeypot: kolom tersembunyi "website" yang hanya diisi bot;
 * - kode keamanan angka (NumericCaptcha) — tanpa ini bot bisa membanjiri
 *   laporan palsu yang masing-masing mengirim notifikasi WA/email ke petugas;
 * - anti kirim ganda: tombol tertekan dua kali (terlihat di log produksi:
 *   2 POST dalam 1 detik → 2 pengaduan yang sama) tidak membuat data baru.
 */
final class PublicFormGuard
{
    private const DUPLICATE_WINDOW_SECONDS = 120;

    /** true bila kiriman berasal dari bot (honeypot terisi). */
    public static function isBot(Request $request): bool
    {
        return filled($request->input('website'));
    }

    /** @throws ValidationException bila kode keamanan salah/kedaluwarsa. */
    public static function assertCaptcha(Request $request, string $context): void
    {
        if (! NumericCaptcha::check($context, $request->input('captcha'))) {
            throw ValidationException::withMessages([
                'captcha' => 'Kode keamanan tidak sesuai atau sudah kedaluwarsa. Silakan ketik angka pada gambar yang baru.',
            ]);
        }
    }

    /**
     * Jalankan $create sekali saja untuk isi formulir yang sama dari alamat IP
     * yang sama dalam 2 menit. Kiriman ulang mendapat nomor tiket yang sama.
     *
     * @param  callable(): string  $create  membuat data & mengembalikan nomor tiket
     */
    public static function once(Request $request, string $context, array $fields, callable $create): string
    {
        $key = 'form-once:' . $context . ':' . hash('sha256', implode("\x1f", [
            // IP + isi (bukan ID sesi): isi yang persis sama dari jaringan yang
            // sama dalam 2 menit memang kiriman ganda, dan tetap terdeteksi
            // walau browser belum sempat menyimpan cookie sesi.
            $request->ip(),
            ...array_map(fn ($value) => trim((string) $value), $fields),
        ]));

        // add() atomik: hanya permintaan pertama yang membuat data.
        if (! Cache::add($key, '', self::DUPLICATE_WINDOW_SECONDS)) {
            for ($i = 0; $i < 20 && Cache::get($key) === ''; $i++) {
                usleep(100_000); // tunggu permintaan pertama selesai (maks. 2 detik)
            }

            $ticket = (string) Cache::get($key);
            if ($ticket !== '') {
                return $ticket;
            }
        }

        try {
            $ticket = $create();
        } catch (\Throwable $e) {
            Cache::forget($key);
            throw $e;
        }

        Cache::put($key, $ticket, self::DUPLICATE_WINDOW_SECONDS);

        return $ticket;
    }
}
