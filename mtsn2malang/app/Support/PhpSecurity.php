<?php

namespace App\Support;

/**
 * Pemeriksaan versi PHP terhadap kerentanan inti PHP yang diketahui.
 *
 * PHP2Shell (CVE-2026-109618 & CVE-2026-109619): RCE tanpa login lewat
 * korupsi memori saat request berhenti karena kehabisan memori. Ditambal di
 * rilis 22 Okt 2026 (GHSA-w8hw-w9fx-j79w, GHSA-6wmf-4rv5-p3rp). Cabang
 * PHP < 8.2 sudah EOL dan tidak mendapat tambalan.
 *
 * Sengaja tanpa dependensi Laravel: dipakai juga oleh Dockerfile saat build
 * untuk menolak image PHP yang belum ditambal.
 */
final class PhpSecurity
{
    /** Versi minimum yang sudah ditambal per cabang PHP. */
    public const PATCHED_VERSIONS = [
        '8.2' => '8.2.35',
        '8.3' => '8.3.36',
        '8.4' => '8.4.27',
        '8.5' => '8.5.12',
    ];

    public const ADVISORY = 'PHP2Shell (CVE-2026-109618 & CVE-2026-109619)';

    /** Versi minimum aman untuk cabang versi ini, atau null bila cabangnya EOL / tidak dikenal. */
    public static function patchedVersionFor(string $version = PHP_VERSION): ?string
    {
        $branch = implode('.', array_slice(explode('.', $version), 0, 2));

        if (isset(self::PATCHED_VERSIONS[$branch])) {
            return self::PATCHED_VERSIONS[$branch];
        }

        // Cabang yang lebih baru dari daftar ini dirilis setelah tambalan.
        return version_compare($branch, max(array_keys(self::PATCHED_VERSIONS)), '>') ? $branch . '.0' : null;
    }

    public static function isVulnerable(string $version = PHP_VERSION): bool
    {
        $patched = self::patchedVersionFor($version);

        return $patched === null || version_compare($version, $patched, '<');
    }

    /** Jalur eksploitasi CVE-2026-109618 — harus Off (aplikasi tidak memakainya). */
    public static function uploadProgressEnabled(): bool
    {
        return filter_var(ini_get('session.upload_progress.enabled'), FILTER_VALIDATE_BOOLEAN);
    }

    /** Saran versi untuk ditampilkan ke admin. */
    public static function recommendation(string $version = PHP_VERSION): string
    {
        $patched = self::patchedVersionFor($version);

        return $patched === null
            ? 'Cabang PHP ' . $version . ' sudah tidak didukung (EOL) — naikkan ke PHP ' . self::PATCHED_VERSIONS['8.4'] . ' atau lebih baru.'
            : 'Perbarui ke PHP ' . $patched . ' atau lebih baru.';
    }
}
