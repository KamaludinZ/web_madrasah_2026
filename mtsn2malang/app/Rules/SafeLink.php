<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Link tujuan yang aman untuk ditampilkan sebagai href publik: path internal
 * ("/ppid/profil") atau URL http(s) lengkap. Skema lain (javascript:, data:,
 * dll), path relatif tanpa "/", dan link berspasi ditolak.
 */
class SafeLink implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        $value = (string) $value;
        $ok = preg_match('#^/(?!/)\S*$#', $value)                       // path internal (bukan "//host")
            || (preg_match('#^https?://\S+$#i', $value) && filter_var($value, FILTER_VALIDATE_URL));

        if (! $ok) {
            $fail('Gunakan URL lengkap (https://...) atau path internal yang diawali "/".');
        }
    }
}
