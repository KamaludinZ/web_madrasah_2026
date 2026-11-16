<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PageView extends Model
{
    /** Setting yang menyimpan jumlah kunjungan yang sudah dibersihkan. */
    public const ARCHIVED_TOTAL_KEY = 'page_views_archived_total';

    /** Detail kunjungan disimpan sekian hari, sisanya diringkas jadi angka total. */
    public const RETENTION_DAYS = 90;

    protected $fillable = ['page', 'ip_address', 'session_id', 'user_agent', 'viewed_date'];

    protected $casts = ['viewed_date' => 'date'];

    /**
     * Hapus DETAIL kunjungan lebih dari 90 hari (per sesi/halaman/hari) agar
     * tabel page_views tidak terus membesar. Jumlahnya lebih dulu ditambahkan
     * ke angka arsip, sehingga "Total Pengunjung" tidak ikut berkurang.
     * Dijalankan harian lewat scheduler (routes/console.php).
     *
     * @return int jumlah baris yang dibersihkan
     */
    public static function pruneOld(): int
    {
        $cutoff = now()->subDays(self::RETENTION_DAYS)->toDateString();

        return DB::transaction(function () use ($cutoff) {
            $old = static::where('viewed_date', '<', $cutoff);
            $count = (clone $old)->count();

            if ($count > 0) {
                Setting::set(self::ARCHIVED_TOTAL_KEY, static::archivedTotal() + $count);
                $old->delete();
            }

            return $count;
        });
    }

    public static function archivedTotal(): int
    {
        return (int) Setting::get(self::ARCHIVED_TOTAL_KEY, 0);
    }

    public static function todayCount(): int
    {
        return static::where('viewed_date', today())->count();
    }

    public static function totalCount(): int
    {
        return static::count() + static::archivedTotal();
    }

    /**
     * Angka "Hari Ini" & "Total" untuk footer publik. Di-cache 5 menit agar
     * setiap halaman tidak menghitung ulang (COUNT) seluruh tabel kunjungan.
     *
     * @return array{today: int, total: int}
     */
    public static function publicCounters(): array
    {
        return Cache::remember('page_views_public_counters', 300, fn () => [
            'today' => static::todayCount(),
            'total' => static::totalCount(),
        ]);
    }

    /**
     * Bot mesin pencari, pemantau uptime, dan skrip tidak dihitung sebagai
     * pengunjung — statistik lebih akurat dan database tidak ditulisi setiap
     * kali crawler lewat.
     */
    public static function isBot(?string $userAgent): bool
    {
        if (blank($userAgent)) {
            return true;
        }

        return (bool) preg_match(
            '/bot\b|bot\/|crawl|spider|slurp|scrap|facebookexternalhit|whatsapp|telegram|preview|curl|wget|python|go-http|httpclient|java\/|okhttp|axios|node-fetch|headless|phantom|lighthouse|pingdom|uptime|monitor|semrush|ahrefs|mj12|dotbot|petalbot|bytespider|gptbot|claudebot|ccbot/i',
            $userAgent
        );
    }

    public static function record(string $page = '/'): void
    {
        if (static::isBot(request()->userAgent())) {
            return;
        }

        // Statistik pengunjung tidak boleh membuat halaman gagal dibuka.
        try {
            $sessionId = session()->getId();
            $today = today()->toDateString();
            $page = Str::limit($page, 250, '');

            // Prevent duplicate counting per session per page per day
            $exists = static::where('session_id', $sessionId)
                ->where('page', $page)
                ->where('viewed_date', $today)
                ->exists();

            if (!$exists) {
                static::create([
                    'page' => $page,
                    'ip_address' => request()->ip(),
                    'session_id' => $sessionId,
                    // Kolom varchar(255): sebagian browser bawaan aplikasi
                    // (Instagram/Facebook) mengirim User-Agent lebih panjang.
                    'user_agent' => Str::limit((string) request()->userAgent(), 250, ''),
                    'viewed_date' => $today,
                ]);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
