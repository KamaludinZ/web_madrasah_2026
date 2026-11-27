<?php

namespace App\Console\Commands;

use App\Models\News;
use App\Services\WordpressMediaRepairService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Pulihkan gambar/dokumen berita hasil migrasi WordPress yang masih menunjuk
 * ke …/wp-content/uploads/… (kini rusak). Lihat WordpressMediaRepairService.
 *
 * Contoh:
 *   php artisan wordpress:repair-media --dry-run --download     (cek dulu)
 *   php artisan wordpress:repair-media --from=/backup/wp-content/uploads
 *   php artisan wordpress:repair-media --download
 */
class RepairWordpressMedia extends Command
{
    protected $signature = 'wordpress:repair-media
        {--from= : Folder cadangan WordPress (berisi wp-content/uploads atau isi folder uploads)}
        {--download : Coba ambil salinan publik (CDN Jetpack i0.wp.com & Internet Archive)}
        {--host=* : Domain WordPress lama (bawaan: domain APP_URL)}
        {--remove-missing : Hapus <img> yang tetap tidak ditemukan (bukan tautan dokumen)}
        {--dry-run : Hanya laporan, tidak mengubah apa pun}
        {--report= : Simpan daftar file yang tidak ditemukan ke file CSV ini}';

    protected $description = 'Pulihkan gambar & dokumen berita hasil migrasi WordPress yang masih menunjuk ke wp-content lama';

    public function handle(): int
    {
        $from = $this->option('from');
        if ($from && ! is_dir($from)) {
            $this->error("Folder tidak ditemukan: {$from}");

            return self::FAILURE;
        }

        $hosts = $this->option('host') ?: [parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost'];
        $dryRun = (bool) $this->option('dry-run');

        if (! $from && ! $this->option('download')) {
            $this->warn('Tanpa --from atau --download hanya file yang sudah pernah dipulihkan yang dipakai.');
        }

        $service = new WordpressMediaRepairService($hosts, $from, (bool) $this->option('download'));
        $stats = ['news' => 0, 'changed' => 0, 'recovered' => 0, 'missing' => 0];
        $missingRows = [];

        foreach ($service->affectedNews() as $news) {
            $stats['news']++;
            $result = $service->repairContent((string) $news->content, $dryRun);
            $content = $result['content'];

            if ($this->option('remove-missing') && ! $dryRun) {
                $content = $service->removeMissingImages($content, $result['missing']);
            }

            $stats['recovered'] += count($result['recovered']);
            $stats['missing'] += count($result['missing']);
            foreach ($result['missing'] as $key) {
                $missingRows[] = [$news->id, $news->slug, $key];
            }

            if (! $dryRun && $content !== $news->content) {
                // Query langsung: tanpa observer/event & tanpa mengubah updated_at.
                DB::table((new News)->getTable())->where('id', $news->id)->update(['content' => $content]);
                $stats['changed']++;
            }

            $this->output->write('.');
        }

        $this->newLine(2);
        $this->table(['Berita terdampak', 'Berita diperbarui', 'Referensi dipulihkan', 'Referensi tidak ditemukan'], [[
            $stats['news'], $dryRun ? '(dry-run)' : $stats['changed'], $stats['recovered'], $stats['missing'],
        ]]);

        if ($report = $this->option('report')) {
            $handle = fopen($report, 'w');
            fputcsv($handle, ['news_id', 'slug', 'file']);
            foreach ($missingRows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
            $this->info("Daftar file yang tidak ditemukan: {$report}");
        } elseif ($missingRows !== []) {
            $this->line('Contoh file yang tidak ditemukan (pakai --report=file.csv untuk daftar lengkap):');
            foreach (array_slice($missingRows, 0, 10) as [$id, $slug, $key]) {
                $this->line("  - [{$slug}] {$key}");
            }
        }

        return self::SUCCESS;
    }
}
