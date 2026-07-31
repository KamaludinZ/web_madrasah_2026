<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Subscriber extends Model
{
    protected $fillable = [
        'name',
        'email',
        'unsubscribe_token',
        'is_active',
        'subscribed_at',
        'unsubscribed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Subscriber $subscriber): void {
            if (blank($subscriber->unsubscribe_token)) {
                $subscriber->unsubscribe_token = Str::random(64);
            }

            if (blank($subscriber->subscribed_at)) {
                $subscriber->subscribed_at = now();
            }
        });
    }
}
