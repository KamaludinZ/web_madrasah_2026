<?php

namespace App\Console\Commands;

use App\Support\ImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Finder\Finder;
use Throwable;

/**
 * Memperkecil gambar lama di storage/app/public yang resolusinya terlalu
 * besar (foto kamera 4000+ px, logo 2800 px). Nama & format file tetap,
 * sehingga alamat gambar di database/berita tidak berubah.
 *
 *   php artisan images:optimize --dry-run   (lihat dulu, tidak mengubah file)
 *   php artisan images:optimize
 */
class OptimizeStoredImages extends Command
{
    protected $signature = 'images:optimize
        {--dry-run : Hanya hitung penghematan, file tidak diubah}
        {--path= : Batasi ke satu folder, mis. news/thumbnails}';

    protected $description = 'Perkecil gambar upload lama yang terlalu besar (resolusi & ukuran file) tanpa mengubah alamatnya';

    /** Folder yang tidak disentuh (file sementara/privat). */
    private const SKIP = ['livewire-tmp', 'tmp-uploads', 'backups', 'wordpress-imports'];

    public function handle(): int
    {
        $root = Storage::disk('public')->path('');
        $base = rtrim($root, '/') . '/' . trim((string) $this->option('path'), '/');

        if (! is_dir($base)) {
            $this->error("Folder tidak ditemukan: {$base}");

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $finder = Finder::create()->files()->in($base)->ignoreDotFiles(true)
            ->exclude(self::SKIP)
            ->name('/\.(jpe?g|png|webp)$/i')
            ->size('> 100k');

        $before = $after = $changed = $failed = 0;
        $rows = [];

        foreach ($finder as $file) {
            $relative = ltrim(str_replace('\\', '/', substr($file->getPathname(), strlen(rtrim($root, '/')))), '/');

            try {
                $result = ImageOptimizer::shrinkInPlace($file->getPathname(), ImageOptimizer::maxDimensionFor($relative), $dryRun);
            } catch (Throwable $e) {
                $failed++;
                $this->warn("Gagal: {$relative} ({$e->getMessage()})");

                continue;
            }

            if ($result['changed']) {
                $changed++;
                $before += $result['before'];
                $after += $result['after'];
                $rows[] = [$relative, $this->kb($result['before']), $this->kb($result['after']), $result['reason']];
            }
        }

        usort($rows, fn ($a, $b) => (float) $b[1] <=> (float) $a[1]);
        if ($rows !== []) {
            $this->table(['File', 'Sebelum (KB)', 'Sesudah (KB)', 'Keterangan'], array_slice($rows, 0, 30));
            if (count($rows) > 30) {
                $this->line('… dan ' . (count($rows) - 30) . ' file lainnya.');
            }
        }

        $saved = $before - $after;
        $this->info(($dryRun ? '[DRY-RUN] Akan diperkecil: ' : 'Diperkecil: ')
            . "{$changed} gambar, hemat " . round($saved / 1048576, 1) . ' MB'
            . ($before > 0 ? ' (' . round($saved / $before * 100) . '%)' : '')
            . ($failed ? ", gagal {$failed}" : '') . '.');

        if ($dryRun && $changed > 0) {
            $this->line('Jalankan tanpa --dry-run untuk menerapkan. Disarankan membuat backup dulu.');
        }

        return self::SUCCESS;
    }

    private function kb(int $bytes): string
    {
        return number_format($bytes / 1024, 0, '.', '');
    }
}
