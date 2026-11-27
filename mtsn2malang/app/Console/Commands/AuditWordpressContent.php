<?php

namespace App\Console\Commands;

use App\Models\News;
use App\Services\WordpressMediaRepairService;
use App\Support\ContentSpamDetector;
use Illuminate\Console\Command;

/**
 * Audit konten berita (terutama hasil migrasi WordPress lama yang pernah
 * disusupi): spam judi/kasino, tautan tersembunyi, teks berbahasa asing tidak
 * wajar, sisa shortcode, dan gambar/dokumen yang masih menunjuk ke wp-content.
 *
 *   php artisan wordpress:audit-content              (laporan saja)
 *   php artisan wordpress:audit-content --unpublish  (spam → status Arsip)
 */
class AuditWordpressContent extends Command
{
    protected $signature = 'wordpress:audit-content
        {--unpublish : Ubah status berita yang terdeteksi spam menjadi Arsip (tidak dihapus)}';

    protected $description = 'Audit konten berita: spam/judi, tautan tersembunyi, shortcode WordPress, dan media wp-content yang rusak';

    public function handle(): int
    {
        $hosts = [parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost'];
        $media = new WordpressMediaRepairService($hosts);

        $spam = [];
        $shortcodes = $brokenMedia = $brokenRefs = 0;

        News::query()->orderBy('id')->lazyById(100)->each(function (News $news) use (&$spam, &$shortcodes, &$brokenMedia, &$brokenRefs, $media) {
            $reasons = ContentSpamDetector::reasons($news->title, (string) $news->content);
            if ($reasons !== []) {
                $spam[] = [$news->id, $news->status, $news->slug, implode('; ', $reasons)];
            }

            if (preg_match('#\[(?:gallery|embed|caption|video)\b#i', (string) $news->content)) {
                $shortcodes++;
            }

            $refs = count($media->findReferences((string) $news->content));
            if ($refs > 0) {
                $brokenMedia++;
                $brokenRefs += $refs;
            }
        });

        $this->info('Berita terindikasi SPAM: ' . count($spam));
        if ($spam !== []) {
            $this->table(['ID', 'Status', 'Slug', 'Alasan'], $spam);
        }

        $this->line("Berita dengan shortcode WordPress (dirapikan otomatis saat tampil): {$shortcodes}");
        $this->line("Berita dengan gambar/dokumen wp-content lama: {$brokenMedia} ({$brokenRefs} file) → jalankan: php artisan wordpress:repair-media --dry-run --download");

        if ($this->option('unpublish') && $spam !== []) {
            $ids = array_column($spam, 0);
            News::whereIn('id', $ids)->where('status', '!=', 'archived')->get()->each(
                fn (News $news) => $news->update(['status' => 'archived'])
            );
            $this->warn(count($ids) . ' berita spam dipindahkan ke status Arsip (tidak tampil di website). Periksa lalu hapus permanen dari panel admin bila benar spam.');
        } elseif ($spam !== []) {
            $this->line('Tambahkan --unpublish untuk menyembunyikan berita spam dari website.');
        }

        return self::SUCCESS;
    }
}
