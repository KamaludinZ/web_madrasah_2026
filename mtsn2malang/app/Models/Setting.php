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

    public static function get(string $key, mixed $default = null): mixed
    {
        // Cache menyimpan nilai asli dari database (null jika belum ada),
        // bukan $default — supaya pemanggil lain dengan default berbeda tidak
        // ikut menerima default milik pemanggil pertama. Dibungkus array agar
        // nilai null juga ikut di-cache.
        $cached = Cache::remember(static::cacheKey($key), 3600, function () use ($key) {
            return ['value' => static::where('key', $key)->value('value')];
        });

        return $cached['value'] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(static::cacheKey($key));
        Cache::forget("setting_{$key}"); // format cache lama
    }

    private static function cacheKey(string $key): string
    {
        return "setting_v2_{$key}";
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
