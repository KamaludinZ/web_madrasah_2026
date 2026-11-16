<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    /**
     * Setting berisi rahasia (password/API key). Disimpan terenkripsi dan
     * tidak pernah dikirim kembali ke browser di form pengaturan.
     */
    public const SECRET_KEYS = ['mail_password', 'gemini_api_key', 'openai_api_key', 'wa_fonnte_token', 'wa_balesotomatis_api_key'];

    protected $fillable = ['key', 'value', 'type', 'group', 'label'];

    /** Kunci cache yang menyimpan SELURUH setting dalam satu entri. */
    public const CACHE_KEY = 'settings_v3_all';

    protected static function booted(): void
    {
        // Perubahan lewat model (set(), seeder, Filament) langsung berlaku.
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::values()[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        static::flushCache();
    }

    public static function flushCache(): void
    {
        Cache::forget(static::CACHE_KEY);
        app()->forgetInstance('settings.values');
    }

    /**
     * Semua setting (key => value) dimuat SEKALI per request / per job antrian:
     * satu baca cache, bukan satu query per setting (halaman publik memanggil
     * Setting::get puluhan kali). `scoped` direset otomatis tiap job antrian
     * sehingga worker yang berjalan lama tidak memakai nilai basi.
     *
     * @return array<string, mixed>
     */
    protected static function values(): array
    {
        $app = app();

        if (! $app->bound('settings.values')) {
            $app->scoped('settings.values', fn () => Cache::remember(
                static::CACHE_KEY,
                3600,
                fn () => static::query()->pluck('value', 'key')->all(),
            ));
        }

        return $app->make('settings.values');
    }

    /**
     * Ambil setting rahasia yang terenkripsi. Nilai lama yang masih tersimpan
     * polos (sebelum enkripsi diterapkan) tetap terbaca apa adanya.
     */
    public static function getSecret(string $key, mixed $default = null): mixed
    {
        $value = static::get($key);

        if (blank($value)) {
            return $default;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value;
        }
    }

    public static function setSecret(string $key, ?string $value): void
    {
        static::set($key, blank($value) ? null : Crypt::encryptString($value));
    }
}
