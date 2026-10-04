<?php

namespace App\Services;

use App\Models\Setting;

/**
 * Profil madrasah (sambutan kepala, identitas, visi, misi, tujuan) — satu
 * sumber data dari Setting yang diisi admin di Konten → Sambutan & Profil.
 * Dipakai Tab Tentang Halaman Portal; bentuk datanya sama dengan yang dibaca
 * section profil di Beranda, sehingga isinya tidak digandakan.
 *
 * Nilai kosong dibiarkan null/[] agar tampilan menyembunyikan bagiannya.
 */
class MadrasahProfileService
{
    /**
     * Peta field Setting → bagian Tab Tentang. Semua field di sini diisi admin
     * di Konten → Sambutan & Profil (ManageWelcome), kecuali "logo" yang
     * diisi di Pengaturan Website → Umum. Dijaga oleh MadrasahProfileServiceTest
     * agar tidak ada field yang dibaca tampilan tapi hilang dari form admin.
     */
    public const FIELD_MAP = [
        'sambutan' => [
            'welcome_headmaster_photo', 'welcome_headmaster_name', 'welcome_headmaster_period',
            'welcome_headmaster_message', 'welcome_headmaster_motto',
        ],
        'identitas' => [
            'identity_nomenklatur', 'identity_npsn', 'identity_nsm', 'identity_akreditasi',
            'identity_address', 'identity_website', 'identity_adiwiyata', 'identity_zona_integritas',
            'identity_npsn_url', 'identity_nsm_url',
        ],
        'visi_misi' => ['visi', 'misi'],
        'tujuan'    => ['tujuan'],
    ];

    public function sambutan(): array
    {
        $photo = Setting::get('welcome_headmaster_photo');

        return [
            'photo'   => filled($photo) ? asset('storage/' . $photo) : null,
            'name'    => Setting::get('welcome_headmaster_name'),
            'period'  => Setting::get('welcome_headmaster_period'),
            'message' => Setting::get('welcome_headmaster_message'),
            'motto'   => Setting::get('welcome_headmaster_motto'),
        ];
    }

    /** [label => nilai], hanya yang terisi, urutan tampil tetap. */
    public function identitas(): array
    {
        $nomenklatur = Setting::get('identity_nomenklatur');

        return array_filter([
            'Nomenklatur'     => $nomenklatur,
            'Jenjang'         => static::jenjangFromNomenklatur($nomenklatur),
            'NPSN'            => Setting::get('identity_npsn'),
            'NSM'             => Setting::get('identity_nsm'),
            'Akreditasi'      => Setting::get('identity_akreditasi'),
            'Alamat'          => Setting::get('identity_address'),
            'Website'         => Setting::get('identity_website'),
            'Adiwiyata'       => Setting::get('identity_adiwiyata'),
            'Zona Integritas' => Setting::get('identity_zona_integritas'),
        ], 'filled');
    }

    /** Tautan profil resmi (hanya URL http/https). */
    public function identitasLinks(): array
    {
        return array_filter([
            'Profil NPSN' => Setting::get('identity_npsn_url'),
            'Profil NSM'  => Setting::get('identity_nsm_url'),
        ], fn ($url) => filled($url) && preg_match('#^https?://#i', $url));
    }

    public function logoUrl(): ?string
    {
        $logo = Setting::get('logo');

        return filled($logo) ? asset('storage/' . $logo) : null;
    }

    /** Data lengkap Tab Tentang (bentuk yang dipakai view pages.portal). */
    public function forTentangTab(): array
    {
        return [
            'sambutan'       => $this->sambutan(),
            'identitas'      => $this->identitas(),
            'identitasLogo'  => $this->logoUrl(),
            'identitasLinks' => $this->identitasLinks(),
            'visi'           => Setting::get('visi'),
            'misi'           => Setting::get('misi'),
            'tujuan'         => Setting::get('tujuan'),
        ];
    }

    /**
     * Jenjang diturunkan dari nomenklatur resmi (tidak ada isian terpisah),
     * mis. "Madrasah Tsanawiyah Negeri 2 …" → setingkat SMP.
     */
    public static function jenjangFromNomenklatur(?string $nomenklatur): ?string
    {
        return match (true) {
            blank($nomenklatur) => null,
            (bool) preg_match('/ibtidaiyah/i', $nomenklatur) => 'Madrasah Ibtidaiyah (setingkat SD)',
            (bool) preg_match('/tsanawiyah/i', $nomenklatur) => 'Madrasah Tsanawiyah (setingkat SMP)',
            (bool) preg_match('/aliyah/i', $nomenklatur)     => 'Madrasah Aliyah (setingkat SMA)',
            default => null,
        };
    }
}
