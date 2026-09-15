<?php

namespace App\Support;

/**
 * Satu sumber acuan resmi untuk label, emoji, dan warna tiap role — dipakai
 * di UserResource, menu akun admin, kartu profil, dan badge penulis publik.
 *
 * Sebelum kelas ini ada, tiap tempat mendefinisikan peta role sendiri-sendiri
 * dan sudah terbukti drift (UserResource menampilkan "Super Admin" di kolom
 * tabel tapi "Super Administrator" di form). Label di sini mengikuti
 * README.md ("Role & Akses"), yang juga konsisten dengan 5 dari 6 lokasi
 * kode yang sudah ada. Emoji & warna mengikuti konsensus yang sudah dipakai
 * di profile-info-card.blade.php dan edit-profile.blade.php.
 */
class RoleDisplay
{
    private const ROLES = [
        'super_admin' => [
            'label' => 'Super Administrator',
            'emoji' => '👑',
            'color' => 'red',
        ],
        'editor_in_chief' => [
            'label' => 'Editor in Chief',
            'emoji' => '✍️',
            'color' => 'violet',
        ],
        'editor' => [
            'label' => 'Editor',
            'emoji' => '📝',
            'color' => 'blue',
        ],
        'contributor' => [
            'label' => 'Kontributor',
            'emoji' => '🖊️',
            'color' => 'emerald',
        ],
        'moderator' => [
            'label' => 'Moderator',
            'emoji' => '🛡️',
            'color' => 'amber',
        ],
    ];

    private const FALLBACK = [
        'label' => '—',
        'emoji' => '👤',
        'color' => 'gray',
    ];

    public static function label(?string $role): string
    {
        if ($role === null) {
            return self::FALLBACK['label'];
        }

        // Role dikenal → label resmi. Role baru yang belum didaftarkan di sini
        // (mis. ditambah lewat Filament Shield) tetap tampil rapi, bukan "—".
        return self::ROLES[$role]['label'] ?? ucfirst(str_replace('_', ' ', $role));
    }

    public static function emoji(?string $role): string
    {
        return self::ROLES[$role ?? '']['emoji'] ?? self::FALLBACK['emoji'];
    }

    /** Nama warna Tailwind/Filament (mis. "red", "violet") untuk role ini. */
    public static function color(?string $role): string
    {
        return self::ROLES[$role ?? '']['color'] ?? self::FALLBACK['color'];
    }

    public static function labelWithEmoji(?string $role): string
    {
        return self::emoji($role) . ' ' . self::label($role);
    }

    /**
     * Kelas Tailwind bg/text/dot (light+dark) untuk kartu ringkas seperti
     * profile-info-card.blade.php. Ditulis literal (bukan interpolasi
     * string) supaya scanner Tailwind JIT tetap mendeteksinya.
     */
    public static function badgeClasses(?string $role): array
    {
        return match (self::color($role)) {
            'red' => [
                'bg' => 'bg-red-50 dark:bg-red-900/20',
                'text' => 'text-red-700 dark:text-red-300',
                'dot' => 'bg-red-400',
            ],
            'violet' => [
                'bg' => 'bg-violet-50 dark:bg-violet-900/20',
                'text' => 'text-violet-700 dark:text-violet-300',
                'dot' => 'bg-violet-400',
            ],
            'blue' => [
                'bg' => 'bg-blue-50 dark:bg-blue-900/20',
                'text' => 'text-blue-700 dark:text-blue-300',
                'dot' => 'bg-blue-400',
            ],
            'emerald' => [
                'bg' => 'bg-emerald-50 dark:bg-emerald-900/20',
                'text' => 'text-emerald-700 dark:text-emerald-300',
                'dot' => 'bg-emerald-400',
            ],
            'amber' => [
                'bg' => 'bg-amber-50 dark:bg-amber-900/20',
                'text' => 'text-amber-700 dark:text-amber-300',
                'dot' => 'bg-amber-400',
            ],
            default => [
                'bg' => 'bg-gray-50 dark:bg-gray-700/30',
                'text' => 'text-gray-600 dark:text-gray-300',
                'dot' => 'bg-gray-400',
            ],
        };
    }

    /**
     * Kelas badge bercincin (ring) untuk header profil seperti
     * edit-profile.blade.php. Literal per warna, alasan sama seperti di atas.
     */
    public static function ringBadgeClasses(?string $role): string
    {
        return match (self::color($role)) {
            'red' => 'bg-red-50 text-red-700 ring-1 ring-red-200 dark:bg-red-900/30 dark:text-red-300 dark:ring-red-800',
            'violet' => 'bg-violet-50 text-violet-700 ring-1 ring-violet-200 dark:bg-violet-900/30 dark:text-violet-300 dark:ring-violet-800',
            'blue' => 'bg-blue-50 text-blue-700 ring-1 ring-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:ring-blue-800',
            'emerald' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:ring-emerald-800',
            'amber' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:ring-amber-800',
            default => 'bg-gray-100 text-gray-600 ring-1 ring-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:ring-gray-600',
        };
    }

    /** @return array<string, string> Untuk dropdown/select: ['super_admin' => 'Super Administrator', ...] */
    public static function options(): array
    {
        return array_map(fn (array $role) => $role['label'], self::ROLES);
    }
}
