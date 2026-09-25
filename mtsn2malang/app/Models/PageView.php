<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PageView extends Model
{
    protected $fillable = ['page', 'ip_address', 'session_id', 'user_agent', 'viewed_date'];

    protected $casts = ['viewed_date' => 'date'];

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
