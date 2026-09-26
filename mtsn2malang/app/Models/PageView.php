<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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

    public static function record(string $page = '/'): void
    {
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
