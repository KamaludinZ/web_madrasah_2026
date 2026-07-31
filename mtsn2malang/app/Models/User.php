<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'bio',
        'is_active',
        'is_suspended',
        'otp_code',
        'otp_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_suspended' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && !$this->is_suspended;
    }

    public function news()
    {
        return $this->hasMany(News::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=1a7a3c&color=fff';
    }

    public function generateOtp(): string
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->update([
            'otp_code'       => Hash::make($otp), // disimpan sebagai hash, bukan plaintext
            'otp_expires_at' => now()->addMinutes(config('auth.otp_expiry', 5)),
        ]);
        return $otp;
    }

    public function verifyOtp(string $otp): bool
    {
        if (! $this->otp_expires_at?->isFuture()) {
            return false;
        }
        // Hash::check mencegah timing attack
        return Hash::check($otp, $this->otp_code);
    }
}
