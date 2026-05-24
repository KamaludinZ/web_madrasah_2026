<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        $sessionId = session()->getId();
        $today = today()->toDateString();

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
                'user_agent' => request()->userAgent(),
                'viewed_date' => $today,
            ]);
        }
    }
}
