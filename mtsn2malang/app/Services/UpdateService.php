<?php

namespace App\Services;

use App\Models\Setting;
use App\Support\AppVersion;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * Cek & terapkan update aplikasi — dari GitHub Releases (repo privat) atau
 * dari ZIP yang diunggah manual oleh admin (applyManualUpdate, untuk kondisi
 * repo tidak dipakai atau perlu terapkan hotfix yang belum dirilis sebagai
 * tag GitHub).
 *
 * Deploy proyek ini historisnya manual (upload file lewat FTP/file manager,
 * lalu composer install + migrate lewat SSH/Terminal cPanel — lihat
 * DEPLOYMENT.md), BUKAN git clone di server. Karena itu update di sini
 * bekerja dari ZIP (tidak butuh git terpasang di server), lalu menerapkan
 * urutan yang SAMA seperti langkah manual di DEPLOYMENT.md: timpa kode
 * aplikasi → composer install → migrate → storage:link → clear+cache.
 *
 * File/folder yang TIDAK PERNAH ditimpa oleh update (menjaga data & config
 * lokal server): .env, storage/, public/storage, public/hot, vendor/,
 * node_modules/, .git — sama persis daftar "JANGAN TIMPA" di DEPLOYMENT.md.
 */
class UpdateService
{
    private const EXCLUDED_PATHS = [
        '.env',
        '.git',
        'storage',
        'vendor',
        'node_modules',
        'public/storage',
        'public/hot',
        'VERSION', // ditulis terpisah setelah update sukses, bukan ditimpa mentah
    ];

    public function repo(): ?string
    {
        return Setting::get('update_github_repo', 'KamaludinZ/web_madrasah_2026');
    }

    public function setRepo(string $repo): void
    {
        Setting::set('update_github_repo', trim($repo));
    }

    public function hasToken(): bool
    {
        return filled(Setting::get('update_github_token_encrypted'));
    }

    public function setToken(string $token): void
    {
        Setting::set('update_github_token_encrypted', $token === '' ? null : Crypt::encryptString($token));
    }

    public function token(): ?string
    {
        $encrypted = Setting::get('update_github_token_encrypted');

        if (blank($encrypted)) {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array{current: string, latest: ?string, available: bool, tag: ?string, notes: ?string, publishedAt: ?string}
     */
    public function checkForUpdate(): array
    {
        $current = AppVersion::current();
        $repo = $this->repo();

        if (blank($repo)) {
            throw new RuntimeException('Repository GitHub belum dikonfigurasi.');
        }

        $token = $this->token();
        if (blank($token)) {
            throw new RuntimeException('GitHub Token belum diisi. Repo ini privat, token wajib untuk mengecek update.');
        }

        $response = Http::withToken($token)
            ->withHeaders(['Accept' => 'application/vnd.github+json', 'X-GitHub-Api-Version' => '2022-11-28'])
            ->timeout(20)
            ->get("https://api.github.com/repos/{$repo}/releases/latest");

        if ($response->status() === 404) {
            return [
                'current' => $current, 'latest' => null, 'available' => false,
                'tag' => null, 'notes' => null, 'publishedAt' => null,
            ];
        }

        if ($response->status() === 401) {
            throw new RuntimeException('GitHub Token tidak valid atau kedaluwarsa.');
        }

        if ($response->status() === 403) {
            throw new RuntimeException('Akses GitHub API ditolak — token mungkin tidak punya izin "repo", atau rate limit tercapai.');
        }

        if (! $response->successful()) {
            throw new RuntimeException('Gagal menghubungi GitHub API (HTTP ' . $response->status() . ').');
        }

        $data = $response->json();
        $tag = ltrim((string) ($data['tag_name'] ?? ''), 'vV');

        if ($tag === '') {
            throw new RuntimeException('Release GitHub tidak punya tag versi yang valid.');
        }

        $available = version_compare($tag, $current, '>');

        return [
            'current' => $current,
            'latest' => $tag,
            'available' => $available,
            'tag' => $data['tag_name'],
            'notes' => $data['body'] ?? null,
            'publishedAt' => $data['published_at'] ?? null,
        ];
    }

    /**
     * @return array{fromVersion: string, toVersion: string, backupFilename: string, shellAvailable: bool}
     */
    public function applyUpdate(string $releaseTag, BackupService $backupService, ?callable $onProgress = null): array
    {
        $progress = $onProgress ?? function (string $_) {};

        $repo = $this->repo();
        $token = $this->token();

        if (blank($repo) || blank($token)) {
            throw new RuntimeException('Konfigurasi GitHub (repo/token) belum lengkap.');
        }

        $fromVersion = AppVersion::current();
        $toVersion = ltrim($releaseTag, 'vV');

        $progress('Membuat backup pengaman sebelum update...');
        $backup = $backupService->create(fn (string $m) => $progress("  {$m}"));
        $backupService->recordCompleted($backup, 'pre_update');

        $tmpDir = storage_path('app/tmp-update-' . uniqid());
        mkdir($tmpDir, 0755, true);
        $zipPath = $tmpDir . '/release.zip';

        try {
            $progress("Mengunduh release {$releaseTag} dari GitHub...");
            $this->downloadReleaseZip($repo, $releaseTag, $token, $zipPath);

            $progress('Mengekstrak & menerapkan berkas release (kode saja, data lokal tidak disentuh)...');
            $this->extractAndSync($zipPath, $tmpDir);

            $shellAvailable = $this->finalizeUpdate($toVersion, $progress);

            return [
                'fromVersion' => $fromVersion,
                'toVersion' => $toVersion,
                'backupFilename' => $backup['filename'],
                'shellAvailable' => $shellAvailable,
            ];
        } finally {
            File::deleteDirectory($tmpDir);
        }
    }

    /**
     * Update manual: admin unggah ZIP kode aplikasi sendiri (tidak lewat GitHub).
     * Berguna kalau repo tidak dipakai, atau saat perlu terapkan patch/hotfix
     * yang belum sempat dirilis sebagai tag GitHub.
     *
     * Struktur ZIP fleksibel: boleh berisi file project langsung di root ZIP,
     * atau dibungkus satu folder (seperti hasil "zip -r" dari luar direktori
     * project, atau zipball ala GitHub) — dideteksi otomatis, sama seperti
     * applyUpdate(). Divalidasi dulu (harus ada composer.json + artisan) supaya
     * ZIP yang salah/bukan kode aplikasi tidak sampai menimpa apa pun.
     *
     * @return array{fromVersion: string, toVersion: string, backupFilename: string, shellAvailable: bool}
     */
    public function applyManualUpdate(string $uploadedZipAbsolutePath, string $toVersion, BackupService $backupService, ?callable $onProgress = null): array
    {
        $progress = $onProgress ?? function (string $_) {};

        if (! is_file($uploadedZipAbsolutePath)) {
            throw new RuntimeException('File ZIP tidak ditemukan.');
        }

        $toVersion = trim($toVersion);
        if ($toVersion === '' || ! preg_match('/^\d+\.\d+\.\d+/', $toVersion)) {
            throw new RuntimeException('Nomor versi tidak valid. Gunakan format semver, mis. 1.1.0.');
        }

        $fromVersion = AppVersion::current();

        if (version_compare($toVersion, $fromVersion, '<=')) {
            throw new RuntimeException("Versi baru ({$toVersion}) harus lebih tinggi dari versi saat ini ({$fromVersion}).");
        }

        $progress('Membuat backup pengaman sebelum update...');
        $backup = $backupService->create(fn (string $m) => $progress("  {$m}"));
        $backupService->recordCompleted($backup, 'pre_update');

        $tmpDir = storage_path('app/tmp-manual-update-' . uniqid());
        mkdir($tmpDir, 0755, true);

        try {
            $progress('Memvalidasi & mengekstrak ZIP...');
            $sourceRoot = $this->extractAndValidate($uploadedZipAbsolutePath, $tmpDir);

            $progress('Menerapkan berkas aplikasi baru (kode saja, data lokal tidak disentuh)...');
            $this->syncApplicationFiles($sourceRoot, base_path());

            $shellAvailable = $this->finalizeUpdate($toVersion, $progress);

            return [
                'fromVersion' => $fromVersion,
                'toVersion' => $toVersion,
                'backupFilename' => $backup['filename'],
                'shellAvailable' => $shellAvailable,
            ];
        } finally {
            File::deleteDirectory($tmpDir);
        }
    }

    private function downloadReleaseZip(string $repo, string $tag, string $token, string $destination): void
    {
        $response = Http::withToken($token)
            ->withHeaders(['Accept' => 'application/vnd.github+json'])
            ->timeout(120)
            ->withOptions(['sink' => $destination])
            ->get("https://api.github.com/repos/{$repo}/zipball/{$tag}");

        if (! $response->successful()) {
            throw new RuntimeException('Gagal mengunduh release dari GitHub (HTTP ' . $response->status() . ').');
        }

        if (! is_file($destination) || filesize($destination) === 0) {
            throw new RuntimeException('Berkas release yang diunduh kosong/rusak.');
        }
    }

    /**
     * Ekstrak ZIP ke $tmpDir/extracted dan terapkan langsung ke base_path().
     * Dipakai applyUpdate() (GitHub) — tidak divalidasi struktur karena
     * sumbernya sudah dipastikan repo yang benar via API.
     */
    private function extractAndSync(string $zipPath, string $tmpDir): void
    {
        $sourceRoot = $this->extractOnly($zipPath, $tmpDir);
        $this->syncApplicationFiles($sourceRoot, base_path());
    }

    /**
     * Ekstrak ZIP lalu validasi isinya benar-benar terlihat seperti kode
     * aplikasi Laravel (ada composer.json + artisan) sebelum dipakai untuk
     * menimpa apa pun — baris pertahanan untuk update manual yang sumbernya
     * file apa saja dari admin, bukan API GitHub yang sudah terpercaya.
     */
    private function extractAndValidate(string $zipPath, string $tmpDir): string
    {
        $sourceRoot = $this->extractOnly($zipPath, $tmpDir);

        if (! is_file($sourceRoot . '/composer.json') || ! is_file($sourceRoot . '/artisan')) {
            throw new RuntimeException(
                'File ZIP ini tidak terlihat seperti kode aplikasi Laravel (composer.json / artisan tidak ditemukan). Update dibatalkan, tidak ada yang ditimpa.'
            );
        }

        return $sourceRoot;
    }

    private function extractOnly(string $zipPath, string $tmpDir): string
    {
        $extractDir = $tmpDir . '/extracted';
        mkdir($extractDir, 0755, true);

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('Gagal membuka arsip ZIP.');
        }
        $zip->extractTo($extractDir);
        $zip->close();

        // ZIP boleh membungkus isinya dalam satu folder root (zipball GitHub,
        // atau hasil "zip -r" dari luar direktori project) — deteksi otomatis.
        $entries = array_values(array_diff(scandir($extractDir) ?: [], ['.', '..']));

        return (count($entries) === 1 && is_dir($extractDir . '/' . $entries[0]))
            ? $extractDir . '/' . $entries[0]
            : $extractDir;
    }

    /**
     * Langkah akhir yang sama untuk update via GitHub maupun manual: jalankan
     * composer install + migrate + cache (kalau shell tersedia), lalu tulis
     * versi baru ke file VERSION.
     */
    private function finalizeUpdate(string $toVersion, callable $progress): bool
    {
        $shellAvailable = $this->shellExecAvailable();

        if ($shellAvailable) {
            $progress('Menjalankan composer install...');
            $this->runComposerInstall($progress);

            $progress('Menjalankan migrasi database...');
            Artisan::call('migrate', ['--force' => true]);
            $progress(trim(Artisan::output()) ?: '  (tidak ada migrasi baru)');

            $progress('Memastikan storage symlink & cache...');
            Artisan::call('storage:link');
            Artisan::call('optimize:clear');
        } else {
            $progress('⚠ shell_exec tidak tersedia di server ini — composer install & migrate TIDAK dijalankan otomatis.');
            $progress('  Jalankan manual via SSH: composer install --no-dev && php artisan migrate --force && php artisan optimize:clear');
        }

        AppVersion::write($toVersion);
        $progress("Versi aplikasi diperbarui ke {$toVersion}.");

        return $shellAvailable;
    }

    private function syncApplicationFiles(string $sourceDir, string $destDir): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relative = ltrim(substr($item->getPathname(), strlen(rtrim($sourceDir, '/'))), '/');

            if ($this->isExcluded($relative)) {
                continue;
            }

            $target = $destDir . '/' . $relative;

            if ($item->isDir()) {
                if (! is_dir($target)) {
                    mkdir($target, 0755, true);
                }
            } else {
                if (! is_dir(dirname($target))) {
                    mkdir(dirname($target), 0755, true);
                }
                copy($item->getPathname(), $target);
            }
        }
    }

    private function isExcluded(string $relativePath): bool
    {
        foreach (self::EXCLUDED_PATHS as $excluded) {
            if ($relativePath === $excluded || str_starts_with($relativePath, $excluded . '/')) {
                return true;
            }
        }

        return false;
    }

    private function shellExecAvailable(): bool
    {
        if (! function_exists('proc_open') || ! function_exists('proc_close')) {
            return false;
        }

        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));

        return ! in_array('proc_open', $disabled, true);
    }

    private function runComposerInstall(callable $progress): void
    {
        $composerBinary = base_path('composer.phar');
        $command = is_file($composerBinary)
            ? ['php', $composerBinary, 'install', '--no-dev', '--optimize-autoloader', '--no-interaction']
            : ['composer', 'install', '--no-dev', '--optimize-autoloader', '--no-interaction'];

        $process = new Process($command, base_path());
        $process->setTimeout(900);

        try {
            $process->run(function ($type, $buffer) use ($progress) {
                $progress('  ' . trim($buffer));
            });
        } catch (\Symfony\Component\Process\Exception\ProcessFailedException|\RuntimeException $e) {
            throw new RuntimeException('composer install gagal dijalankan: ' . $e->getMessage());
        }

        if (! $process->isSuccessful()) {
            throw new RuntimeException('composer install gagal: ' . $process->getErrorOutput());
        }
    }
}
