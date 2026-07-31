<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Popup extends Model
{
    protected $fillable = [
        'title', 'type', 'is_active', 'show_once',
        'start_date', 'end_date', 'order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'show_once'  => 'boolean',
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PopupItem::class)->orderBy('order');
    }

    /**
     * Scope: popup yang aktif dan dalam rentang tanggal berlaku.
     */
    public function scopeActive(Builder $query): Builder
    {
        $today = Carbon::today();

        return $query
            ->where('is_active', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
            })
            ->orderBy('order');
    }
}
