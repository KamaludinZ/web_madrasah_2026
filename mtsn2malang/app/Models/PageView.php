<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Str;

class PageView extends Model
{
    use Prunable;

    protected $fillable = ['page', 'ip_address', 'session_id', 'user_agent', 'viewed_date'];

    protected $casts = ['viewed_date' => 'date'];

    /**
     * Data kunjungan lebih dari 90 hari dihapus oleh `php artisan model:prune`
     * (dijadwalkan di routes/console.php). Tanpa trait Prunable perintah itu
     * tidak melakukan apa-apa dan tabel page_views terus membesar.
     */
    public function prunable(): Builder
    {
        return static::where('viewed_date', '<', now()->subDays(90)->toDateString());
    }

    public static function todayCount(): int
    {
        return static::where('viewed_date', today())->count();
    }

    public static function totalCount(): int
    {
        return static::count();
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
