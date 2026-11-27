<?php

namespace App\Services;

use App\Models\News;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Memulihkan gambar & dokumen di konten berita hasil migrasi WordPress yang
 * masih menunjuk ke alamat lama (…/wp-content/uploads/…). Domain lama kini
 * mengarah ke aplikasi ini sehingga file tersebut tidak ada lagi (404/403).
 *
 * Sumber file (berurutan):
 *  1. Folder cadangan WordPress lokal (opsi --from), mis. hasil ekstrak
 *     backup cPanel yang berisi wp-content/uploads.
 *  2. Salinan publik (opsi --download): CDN Jetpack (i0.wp.com) dan Internet
 *     Archive. Host sumber DIKUNCI di sini (bukan dari input) — aman dari SSRF.
 *
 * File divalidasi isinya (gambar/PDF/Office asli), disimpan di
 * storage/app/public/wordpress-media/… lalu URL di konten diganti ke
 * /storage/wordpress-media/… (relatif, tidak bergantung domain).
 */
class WordpressMediaRepairService
{
    public const STORAGE_DIR = 'wordpress-media';

    private const MAX_BYTES = 25 * 1024 * 1024;

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    private const DOCUMENT_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];

    /** @var array<string, string|null> key → URL lokal baru (null = tidak ditemukan) */
    private array $resolved = [];

    /** @param  string[]  $hosts  host WordPress lama, mis. ['mtsn2kotamalang.sch.id'] */
    public function __construct(
        private readonly array $hosts,
        private readonly ?string $fromDirectory = null,
        private readonly bool $download = false,
    ) {}

    /** Pola URL lama: (proxy Jetpack)? (www.)?host /wp-content/(uploads|gallery)/path (?query) */
    public function pattern(): string
    {
        $hosts = implode('|', array_map(
            fn (string $host) => preg_quote(preg_replace('/^www\./i', '', $host), '#'),
            $this->hosts
        ));

        return '#(?:https?:)?//(?:i[0-3]\.wp\.com/)?(?:www\.)?(?:' . $hosts . ')(/wp-content/(?:uploads|gallery)/[^"\'\s?<>]+)(?:\?[^"\'\s<>]*)?#i';
    }

    /** @return string[] key unik (mis. "wp-content/uploads/2024/06/a.jpg") yang dirujuk konten */
    public function findReferences(string $html): array
    {
        preg_match_all($this->pattern(), $html, $matches);

        return array_values(array_unique(array_map(
            fn (string $path) => ltrim(rawurldecode($path), '/'),
            $matches[1]
        )));
    }

    /**
     * @return array{content: string, recovered: string[], missing: string[]}
     */
    public function repairContent(string $html, bool $dryRun = false): array
    {
        $recovered = $missing = [];

        foreach ($this->findReferences($html) as $key) {
            $url = $this->resolve($key, $dryRun);
            $url === null ? $missing[] = $key : $recovered[] = $key;
        }

        if ($dryRun || $recovered === []) {
            return ['content' => $html, 'recovered' => $recovered, 'missing' => $missing];
        }

        // srcset/sizes WordPress berisi daftar ukuran lain di alamat lama —
        // dibuang agar browser memakai src yang sudah dipulihkan.
        $html = preg_replace_callback(
            '#<img\b[^>]*>#i',
            fn (array $m) => preg_match($this->pattern(), $m[0])
                ? preg_replace('#\s(?:srcset|sizes|data-orig-file|data-medium-file|data-large-file)\s*=\s*("[^"]*"|\'[^\']*\')#i', '', $m[0])
                : $m[0],
            $html
        ) ?? $html;

        $html = preg_replace_callback(
            $this->pattern(),
            function (array $m) {
                $key = ltrim(rawurldecode($m[1]), '/');

                return $this->resolved[$key] ?? $m[0];
            },
            $html
        ) ?? $html;

        return ['content' => $html, 'recovered' => $recovered, 'missing' => $missing];
    }

    /** Ambil file untuk key; kembalikan URL lokal atau null bila tidak ditemukan. */
    public function resolve(string $key, bool $dryRun = false): ?string
    {
        if (array_key_exists($key, $this->resolved)) {
            return $this->resolved[$key];
        }

        $extension = strtolower(pathinfo($key, PATHINFO_EXTENSION));
        if (! in_array($extension, [...self::IMAGE_EXTENSIONS, ...self::DOCUMENT_EXTENSIONS], true) || str_contains($key, '..')) {
            return $this->resolved[$key] = null;
        }

        $storagePath = self::STORAGE_DIR . '/' . preg_replace('#^wp-content/#', '', $key);
        $localUrl = '/storage/' . implode('/', array_map('rawurlencode', explode('/', $storagePath)));

        if (Storage::disk('public')->exists($storagePath)) {
            return $this->resolved[$key] = $localUrl;
        }

        $contents = $this->fromBackup($key) ?? ($this->download ? $this->fromPublicCopies($key) : null);

        if ($contents === null || ! $this->isValidFile($contents, $extension)) {
            return $this->resolved[$key] = null;
        }

        if (! $dryRun) {
            Storage::disk('public')->put($storagePath, $contents);
        }

        return $this->resolved[$key] = $localUrl;
    }

    private function fromBackup(string $key): ?string
    {
        if (! $this->fromDirectory) {
            return null;
        }

        $root = rtrim($this->fromDirectory, '/');
        $relative = preg_replace('#^wp-content/#', '', $key);           // uploads/2024/06/a.jpg
        $withoutFolder = preg_replace('#^(uploads|gallery)/#', '', $relative); // 2024/06/a.jpg

        foreach ($this->candidateNames($key) as $name) {
            foreach ([$root . '/' . $key, $root . '/' . $relative, $root . '/' . $withoutFolder] as $base) {
                $file = dirname($base) . '/' . $name;
                $real = realpath($file);
                // Hanya file biasa di dalam folder cadangan (tidak keluar via symlink/..).
                if ($real && is_file($real) && str_starts_with($real, realpath($root) . '/') && filesize($real) <= self::MAX_BYTES) {
                    return file_get_contents($real) ?: null;
                }
            }
        }

        return null;
    }

    private function fromPublicCopies(string $key): ?string
    {
        $host = 'www.' . preg_replace('/^www\./i', '', $this->hosts[0]);

        foreach ($this->candidateNames($key) as $name) {
            $path = dirname($key) . '/' . $name;
            $encoded = implode('/', array_map('rawurlencode', explode('/', $path)));
            $original = 'https://' . $host . '/' . $encoded;

            foreach ([
                'https://i0.wp.com/' . $host . '/' . $encoded,
                'https://web.archive.org/web/2026id_/' . $original,
            ] as $url) {
                try {
                    $response = Http::timeout(20)->withOptions(['allow_redirects' => ['max' => 5, 'protocols' => ['https']]])->get($url);
                    if ($response->successful() && strlen($response->body()) <= self::MAX_BYTES) {
                        return $response->body();
                    }
                } catch (Throwable) {
                    // coba sumber berikutnya
                }
            }
        }

        return null;
    }

    /** Nama file persis, lalu versi asli tanpa akhiran ukuran WordPress (-1024x640). */
    private function candidateNames(string $key): array
    {
        $name = basename($key);
        $original = preg_replace('/-\d{2,5}x\d{2,5}(?=\.[a-z0-9]+$)/i', '', $name);

        return array_values(array_unique([$name, $original]));
    }

    private function isValidFile(string $contents, string $extension): bool
    {
        if ($contents === '' || strlen($contents) > self::MAX_BYTES) {
            return false;
        }

        if (in_array($extension, self::IMAGE_EXTENSIONS, true)) {
            $info = @getimagesizefromstring($contents);

            return $info !== false && in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP], true);
        }

        return match ($extension) {
            'pdf' => str_starts_with($contents, '%PDF-'),
            'doc', 'xls', 'ppt' => str_starts_with($contents, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1"), // OLE2
            'docx', 'xlsx', 'pptx' => str_starts_with($contents, "PK\x03\x04"),
            default => false,
        };
    }

    /** Hapus <img> yang file-nya tidak bisa dipulihkan (agar tidak tampil ikon gambar rusak). */
    public function removeMissingImages(string $html, array $missingKeys): string
    {
        if ($missingKeys === []) {
            return $html;
        }

        return preg_replace_callback(
            '#(?:<a\b[^>]*>\s*)?<img\b[^>]*>(?:\s*</a>)?#i',
            function (array $m) use ($missingKeys) {
                preg_match($this->pattern(), $m[0], $ref);
                $key = isset($ref[1]) ? ltrim(rawurldecode($ref[1]), '/') : null;

                return $key !== null && in_array($key, $missingKeys, true) ? '' : $m[0];
            },
            $html
        ) ?? $html;
    }

    /** @return iterable<News> */
    public function affectedNews(): iterable
    {
        return News::query()
            ->where(fn ($q) => $q->where('content', 'like', '%wp-content/uploads%')->orWhere('content', 'like', '%wp-content/gallery%'))
            ->orderBy('id')
            ->lazyById(50);
    }
}
