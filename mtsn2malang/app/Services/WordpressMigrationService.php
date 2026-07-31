<?php

namespace App\Services;

use App\Models\Category;
use App\Models\News;
use App\Models\WordpressMigrationBatch;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PDO;
use PDOException;
use Throwable;

class WordpressMigrationService
{
    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Buat koneksi PDO ke database WordPress (online).
     */
    private function makePdo(array $data): PDO
    {
        $host     = trim($data['wp_migration_host'] ?? '127.0.0.1');
        $port     = (int) ($data['wp_migration_port'] ?? 3306);
        $dbname   = trim($data['wp_migration_database'] ?? '');
        $username = trim($data['wp_migration_username'] ?? '');
        $password = $data['wp_migration_db_password'] ?? '';

        if (! $dbname || ! $username) {
            throw new \RuntimeException('Host, database, dan username wajib diisi.');
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT            => 10,
        ]);
    }

    /**
     * Buat koneksi PDO ke database WordPress lokal (offline).
     */
    private function makeOfflinePdo(array $data): PDO
    {
        $host     = trim($data['wp_migration_offline_host'] ?? '127.0.0.1');
        $port     = (int) ($data['wp_migration_offline_port'] ?? 3306);
        $dbname   = trim($data['wp_migration_offline_database'] ?? '');
        $username = trim($data['wp_migration_offline_username'] ?? '');
        $password = $data['wp_migration_offline_password'] ?? '';

        if (! $dbname || ! $username) {
            throw new \RuntimeException('Database lokal dan username wajib diisi untuk migrasi offline via DB.');
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT            => 10,
        ]);
    }

    /**
     * Resolve path file XML offline (dari upload atau path manual).
     */
    private function resolveXmlPath(array $data): string
    {
        // Prioritas 1: path manual
        $manualPath = trim($data['wp_migration_offline_file_path'] ?? '');
        if ($manualPath && file_exists($manualPath)) {
            return $manualPath;
        }

        // Prioritas 2: file upload (Filament FileUpload simpan sebagai string path relatif)
        $uploadedFile = $data['wp_migration_offline_file'] ?? null;
        if ($uploadedFile) {
            // Bisa berupa array (Filament v3) atau string
            $relativePath = is_array($uploadedFile) ? (reset($uploadedFile) ?: '') : (string) $uploadedFile;
            $fullPath = storage_path('app/public/' . ltrim($relativePath, '/'));
            if (file_exists($fullPath)) {
                return $fullPath;
            }
        }

        throw new \RuntimeException(
            'File XML tidak ditemukan. Upload file WXR atau isi path file di server.'
        );
    }

    /**
     * Sanitasi konten HTML dari WordPress agar aman disimpan.
     */
    private function sanitizeContent(string $html): string
    {
        // Hapus script & style tags
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? $html;
        $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $html) ?? $html;
        // Hapus event handler attributes
        $html = preg_replace('/\s+on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html) ?? $html;
        // Hapus javascript: links
        $html = preg_replace('/href\s*=\s*["\']javascript:[^"\']*["\']/i', 'href="#"', $html) ?? $html;

        return trim($html);
    }

    /**
     * Rewrite URL gambar di konten dari domain WordPress lama ke storage lokal.
     */
    private function rewriteContentImages(string $content, string $sourceUrl, array &$downloadedMap): string
    {
        if (! $sourceUrl) {
            return $content;
        }

        $sourceUrl = rtrim($sourceUrl, '/');

        return preg_replace_callback(
            '/(src|href)=["\'](' . preg_quote($sourceUrl, '/') . '\/wp-content\/uploads\/[^"\']+)["\']/i',
            function ($matches) use (&$downloadedMap) {
                $originalUrl = $matches[2];
                if (isset($downloadedMap[$originalUrl])) {
                    return $matches[1] . '="' . $downloadedMap[$originalUrl] . '"';
                }
                return $matches[0];
            },
            $content
        ) ?? $content;
    }

    /**
     * Download gambar dari URL dan simpan ke storage lokal.
     * Return URL lokal atau null jika gagal.
     */
    private function downloadImage(string $url): ?string
    {
        try {
            $response = Http::timeout(15)->get($url);
            if (! $response->successful()) {
                return null;
            }

            $ext      = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $safeExt  = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true) ? $ext : 'jpg';
            $filename = 'wordpress-migration/' . Str::uuid() . '.' . $safeExt;

            Storage::disk('public')->put($filename, $response->body());

            return Storage::disk('public')->url($filename);
        } catch (Throwable) {
            return null;
        }
    }

    // ─── ONLINE: Detect Prefix ───────────────────────────────────────────────

    /**
     * Auto-detect prefix tabel WordPress dari semua tabel di database.
     * Cari tabel yang diakhiri dengan 'posts' — prefix-nya adalah bagian sebelum 'posts'.
     */
    public function detectPrefix(array $data): array
    {
        try {
            $pdo    = $this->makePdo($data);
            $dbname = trim($data['wp_migration_database'] ?? '');

            // Ambil semua tabel di database
            $stmt   = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (empty($tables)) {
                return ['ok' => false, 'message' => 'Database kosong — tidak ada tabel ditemukan.', 'prefix' => null];
            }

            // Cari tabel yang berakhiran 'posts'
            $candidates = [];
            foreach ($tables as $table) {
                if (str_ends_with($table, 'posts')) {
                    $prefix = substr($table, 0, -5); // hapus 'posts' dari akhir
                    // Verifikasi ini benar-benar tabel WordPress dengan cek kolom khas
                    $colStmt = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE 'post_status'");
                    if ($colStmt->rowCount() > 0) {
                        $candidates[] = $prefix;
                    }
                }
            }

            if (empty($candidates)) {
                // Tampilkan semua tabel sebagai petunjuk
                $tableList = implode(', ', array_slice($tables, 0, 20));
                return [
                    'ok'      => false,
                    'message' => "Tidak ditemukan tabel WordPress. Tabel yang ada: {$tableList}",
                    'prefix'  => null,
                    'tables'  => $tables,
                ];
            }

            // Jika ada lebih dari satu kandidat, pilih yang paling pendek (biasanya wp_)
            usort($candidates, fn($a, $b) => strlen($a) - strlen($b));
            $detectedPrefix = $candidates[0];

            $allPrefixes = count($candidates) > 1
                ? ' (kandidat lain: ' . implode(', ', array_slice($candidates, 1)) . ')'
                : '';

            return [
                'ok'      => true,
                'message' => "Prefix terdeteksi: \"{$detectedPrefix}\"{$allPrefixes}. Silakan update field prefix.",
                'prefix'  => $detectedPrefix,
                'tables'  => $tables,
            ];
        } catch (PDOException $e) {
            return ['ok' => false, 'message' => 'Koneksi gagal: ' . $e->getMessage(), 'prefix' => null];
        }
    }

    // ─── ONLINE: Test Koneksi ─────────────────────────────────────────────────

    public function testConnection(array $data): array
    {
        try {
            $pdo    = $this->makePdo($data);
            $prefix = trim($data['wp_migration_prefix'] ?? 'wp_');
            $stmt   = $pdo->query("SHOW TABLES LIKE '{$prefix}posts'");
            $found  = $stmt->rowCount() > 0;

            if ($found) {
                // Hitung jumlah post published sebagai info tambahan
                try {
                    $count = (int) $pdo->query(
                        "SELECT COUNT(*) FROM `{$prefix}posts` WHERE post_status='publish' AND post_type='post'"
                    )->fetchColumn();
                    return [
                        'ok'      => true,
                        'message' => "Koneksi berhasil. Tabel {$prefix}posts ditemukan. Total post published: {$count}.",
                    ];
                } catch (\Throwable) {
                    return ['ok' => true, 'message' => "Koneksi berhasil. Tabel {$prefix}posts ditemukan."];
                }
            }

            // Prefix tidak cocok — coba auto-detect dan beri saran
            $detect = $this->detectPrefix($data);
            $hint   = $detect['prefix']
                ? " Prefix yang terdeteksi: \"{$detect['prefix']}\". Silakan update field prefix lalu coba lagi."
                : " Tidak bisa auto-detect prefix. " . ($detect['message'] ?? '');

            return [
                'ok'             => false,
                'message'        => "Koneksi berhasil, tapi tabel {$prefix}posts tidak ditemukan.{$hint}",
                'detected_prefix' => $detect['prefix'],
            ];
        } catch (PDOException $e) {
            return ['ok' => false, 'message' => 'Koneksi gagal: ' . $e->getMessage()];
        }
    }

    // ─── ONLINE: Preview Stats ────────────────────────────────────────────────

    public function previewStats(array $data): array
    {
        $pdo    = $this->makePdo($data);
        $prefix = trim($data['wp_migration_prefix'] ?? 'wp_');

        $posts = (int) $pdo->query(
            "SELECT COUNT(*) FROM {$prefix}posts WHERE post_status='publish' AND post_type='post'"
        )->fetchColumn();

        $categories = (int) $pdo->query(
            "SELECT COUNT(*) FROM {$prefix}term_taxonomy WHERE taxonomy='category'"
        )->fetchColumn();

        $tags = (int) $pdo->query(
            "SELECT COUNT(*) FROM {$prefix}term_taxonomy WHERE taxonomy='post_tag'"
        )->fetchColumn();

        $attachments = (int) $pdo->query(
            "SELECT COUNT(*) FROM {$prefix}posts WHERE post_type='attachment'"
        )->fetchColumn();

        return compact('posts', 'categories', 'tags', 'attachments');
    }

    // ─── ONLINE: Run Migration ────────────────────────────────────────────────

    public function runMigration(array $data, ?int $existingBatchId = null): array
    {
        // Naikkan limit untuk proses panjang (jika dijalankan sync, bukan via job)
        @set_time_limit(1800);
        @ini_set('memory_limit', '512M');

        $isDryRun   = (bool) ($data['wp_migration_dry_run'] ?? true);
        $prefix     = trim($data['wp_migration_prefix'] ?? 'wp_');
        $sourceUrl  = trim($data['wp_migration_source_url'] ?? '');
        $pdo        = $this->makePdo($data);

        $batch = null;
        if (! $isDryRun) {
            if ($existingBatchId) {
                $batch = WordpressMigrationBatch::find($existingBatchId);
            }
            if (! $batch) {
                $batch = WordpressMigrationBatch::create([
                    'mode'            => 'online',
                    'status'          => 'running',
                    'source_database' => $data['wp_migration_database'] ?? '',
                    'source_prefix'   => $prefix,
                    'started_at'      => now(),
                ]);
            }
        }

        $migratedPosts  = 0;
        $handledImages  = 0;
        $downloadedMap  = [];

        try {
            // ── Kategori ──────────────────────────────────────────────────────
            if ($data['wp_migration_import_categories'] ?? true) {
                $cats = $pdo->query(
                    "SELECT t.name, t.slug FROM {$prefix}terms t
                     JOIN {$prefix}term_taxonomy tt ON t.term_id = tt.term_id
                     WHERE tt.taxonomy = 'category'"
                )->fetchAll();

                if (! $isDryRun) {
                    foreach ($cats as $cat) {
                        Category::firstOrCreate(
                            ['slug' => Str::slug($cat['slug'])],
                            ['name' => $cat['name'], 'is_active' => true]
                        );
                    }
                }
            }

            // ── Posts ─────────────────────────────────────────────────────────
            if ($data['wp_migration_import_posts'] ?? true) {
                $posts = $pdo->query(
                    "SELECT p.ID, p.post_title, p.post_content, p.post_excerpt,
                            p.post_name, p.post_date, p.post_author
                     FROM {$prefix}posts p
                     WHERE p.post_status = 'publish' AND p.post_type = 'post'
                     ORDER BY p.post_date DESC
                     LIMIT 500"
                )->fetchAll();

                foreach ($posts as $post) {
                    $content = $this->sanitizeContent($post['post_content'] ?? '');

                    // Download & rewrite gambar di konten
                    if (($data['wp_migration_import_images'] ?? true) && $sourceUrl) {
                        preg_match_all(
                            '/src=["\'](' . preg_quote($sourceUrl, '/') . '\/wp-content\/uploads\/[^"\']+)["\']/i',
                            $content,
                            $imgMatches
                        );
                        foreach (array_unique($imgMatches[1] ?? []) as $imgUrl) {
                            if (! isset($downloadedMap[$imgUrl])) {
                                $localUrl = $this->downloadImage($imgUrl);
                                if ($localUrl) {
                                    $downloadedMap[$imgUrl] = $localUrl;
                                    $handledImages++;
                                }
                            }
                        }
                    }

                    if (($data['wp_migration_rewrite_content_images'] ?? true) && $sourceUrl) {
                        $content = $this->rewriteContentImages($content, $sourceUrl, $downloadedMap);
                    }

                    // Thumbnail
                    $thumbnailPath = null;
                    if ($data['wp_migration_import_images'] ?? true) {
                        $thumbStmt = $pdo->prepare(
                            "SELECT meta_value FROM {$prefix}postmeta
                             WHERE post_id = ? AND meta_key = '_thumbnail_id' LIMIT 1"
                        );
                        $thumbStmt->execute([$post['ID']]);
                        $thumbId = $thumbStmt->fetchColumn();

                        if ($thumbId) {
                            $thumbUrlStmt = $pdo->prepare(
                                "SELECT guid FROM {$prefix}posts WHERE ID = ? LIMIT 1"
                            );
                            $thumbUrlStmt->execute([$thumbId]);
                            $thumbUrl = $thumbUrlStmt->fetchColumn();

                            if ($thumbUrl) {
                                $localThumb = $this->downloadImage($thumbUrl);
                                if ($localThumb) {
                                    // Simpan path relatif untuk storage
                                    $thumbnailPath = str_replace(
                                        Storage::disk('public')->url(''),
                                        '',
                                        $localThumb
                                    );
                                    $handledImages++;
                                }
                            }
                        }
                    }

                    // Kategori post
                    $catId = null;
                    $catStmt = $pdo->prepare(
                        "SELECT t.name FROM {$prefix}terms t
                         JOIN {$prefix}term_taxonomy tt ON t.term_id = tt.term_id
                         JOIN {$prefix}term_relationships tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
                         WHERE tr.object_id = ? AND tt.taxonomy = 'category' LIMIT 1"
                    );
                    $catStmt->execute([$post['ID']]);
                    $catName = $catStmt->fetchColumn();
                    if ($catName) {
                        $cat   = Category::firstOrCreate(['name' => $catName], ['slug' => Str::slug($catName), 'is_active' => true]);
                        $catId = $cat->id;
                    }

                    if (! $isDryRun) {
                        $slug = Str::slug($post['post_name'] ?: $post['post_title']);
                        // Pastikan slug unik
                        $baseSlug = $slug;
                        $i = 1;
                        while (News::where('slug', $slug)->exists()) {
                            $slug = $baseSlug . '-' . $i++;
                        }

                        News::create([
                            'title'               => strip_tags($post['post_title']),
                            'slug'                => $slug,
                            'content'             => $content,
                            'excerpt'             => strip_tags($post['post_excerpt'] ?? ''),
                            'status'              => 'published',
                            'published_at'        => $post['post_date'],
                            'category_id'         => $catId,
                            'thumbnail'           => $thumbnailPath,
                            'user_id'             => 1,
                            'migration_batch_id'  => $batch?->id,
                        ]);

                        $migratedPosts++;
                    } else {
                        $migratedPosts++;
                    }
                }
            }

            if ($batch) {
                $batch->update([
                    'status'         => 'completed',
                    'migrated_posts' => $migratedPosts,
                    'handled_images' => $handledImages,
                    'finished_at'    => now(),
                    'notes'          => "Migrasi online selesai.",
                ]);
            }

            $label = $isDryRun ? '[DRY RUN] ' : '';
            return [
                'ok'      => true,
                'message' => "{$label}Berhasil memproses {$migratedPosts} post dan {$handledImages} gambar.",
            ];
        } catch (Throwable $e) {
            $batch?->update(['status' => 'failed', 'notes' => $e->getMessage(), 'finished_at' => now()]);
            Log::error('WordPress online migration failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    // ─── OFFLINE: Validasi XML ────────────────────────────────────────────────

    public function testOfflineBackup(array $data): array
    {
        try {
            $xmlPath = $this->resolveXmlPath($data);
            $size    = round(filesize($xmlPath) / 1024 / 1024, 2);

            // Coba baca sebagian XML untuk validasi
            $handle = fopen($xmlPath, 'r');
            $header = fread($handle, 2048);
            fclose($handle);

            $isXml = str_contains($header, '<?xml') || str_contains($header, '<rss');

            if (! $isXml) {
                return ['ok' => false, 'message' => 'File bukan format XML/WXR yang valid.'];
            }

            return ['ok' => true, 'message' => "File XML valid. Ukuran: {$size} MB. Path: {$xmlPath}"];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    // ─── OFFLINE: Preview Stats dari XML ─────────────────────────────────────

    public function previewStatsOffline(array $data): array
    {
        $xmlPath = $this->resolveXmlPath($data);

        // Gunakan XMLReader untuk file besar (hemat memori)
        $reader = new \XMLReader();
        if (! $reader->open($xmlPath)) {
            throw new \RuntimeException('Gagal membuka file XML.');
        }

        $posts       = 0;
        $categories  = 0;
        $tags        = 0;
        $attachments = 0;

        while ($reader->read()) {
            if ($reader->nodeType === \XMLReader::ELEMENT) {
                if ($reader->localName === 'item') {
                    $item = new \SimpleXMLElement($reader->readOuterXml());
                    $ns   = $item->children('wp', true);
                    $type = (string) ($ns->post_type ?? '');

                    match ($type) {
                        'post'       => $posts++,
                        'attachment' => $attachments++,
                        default      => null,
                    };
                } elseif ($reader->localName === 'category' && $reader->namespaceURI === '') {
                    $categories++;
                } elseif ($reader->localName === 'tag' && $reader->namespaceURI === '') {
                    $tags++;
                }
            }
        }

        $reader->close();

        return compact('posts', 'categories', 'tags', 'attachments');
    }

    // ─── OFFLINE: Run Migration dari XML ─────────────────────────────────────

    public function runMigrationOffline(array $data, ?int $existingBatchId = null): array
    {
        // Naikkan limit untuk proses panjang
        @set_time_limit(1800);
        @ini_set('memory_limit', '512M');

        $isDryRun  = (bool) ($data['wp_migration_dry_run'] ?? true);
        $xmlPath   = $this->resolveXmlPath($data);
        $sourceUrl = trim($data['wp_migration_source_url'] ?? '');

        $batch = null;
        if (! $isDryRun) {
            if ($existingBatchId) {
                $batch = WordpressMigrationBatch::find($existingBatchId);
            }
            if (! $batch) {
                $batch = WordpressMigrationBatch::create([
                    'mode'        => 'offline',
                    'status'      => 'running',
                    'source_file' => basename($xmlPath),
                    'started_at'  => now(),
                ]);
            }
        }

        $migratedPosts = 0;
        $handledImages = 0;
        $downloadedMap = [];

        try {
            $reader = new \XMLReader();
            if (! $reader->open($xmlPath)) {
                throw new \RuntimeException('Gagal membuka file XML.');
            }

            // Kumpulkan kategori dulu
            $categoryMap = [];

            while ($reader->read()) {
                if ($reader->nodeType !== \XMLReader::ELEMENT || $reader->localName !== 'item') {
                    continue;
                }

                $xml  = $reader->readOuterXml();
                $item = new \SimpleXMLElement($xml);
                $wp   = $item->children('wp', true);
                $dc   = $item->children('dc', true);

                $postType   = (string) ($wp->post_type ?? '');
                $postStatus = (string) ($wp->status ?? '');

                if ($postType !== 'post' || $postStatus !== 'publish') {
                    continue;
                }

                $title   = (string) $item->title;
                $slug    = (string) ($wp->post_name ?? Str::slug($title));
                $date    = (string) ($wp->post_date ?? now());
                $content = $this->sanitizeContent((string) ($item->children('content', true)->encoded ?? ''));
                $excerpt = strip_tags((string) ($item->children('excerpt', true)->encoded ?? ''));

                // Kategori
                $catName = null;
                foreach ($item->category as $cat) {
                    $domain = (string) $cat->attributes()['domain'];
                    if ($domain === 'category') {
                        $catName = (string) $cat;
                        break;
                    }
                }

                $catId = null;
                if ($catName && ! $isDryRun) {
                    $catSlug = Str::slug($catName);
                    if (! isset($categoryMap[$catSlug])) {
                        $catModel = Category::firstOrCreate(
                            ['slug' => $catSlug],
                            ['name' => $catName, 'is_active' => true]
                        );
                        $categoryMap[$catSlug] = $catModel->id;
                    }
                    $catId = $categoryMap[$catSlug];
                }

                // Download gambar di konten
                if (($data['wp_migration_import_images'] ?? true) && $sourceUrl) {
                    preg_match_all(
                        '/src=["\'](' . preg_quote($sourceUrl, '/') . '\/wp-content\/uploads\/[^"\']+)["\']/i',
                        $content,
                        $imgMatches
                    );
                    foreach (array_unique($imgMatches[1] ?? []) as $imgUrl) {
                        if (! isset($downloadedMap[$imgUrl])) {
                            $localUrl = $this->downloadImage($imgUrl);
                            if ($localUrl) {
                                $downloadedMap[$imgUrl] = $localUrl;
                                $handledImages++;
                            }
                        }
                    }
                }

                if (($data['wp_migration_rewrite_content_images'] ?? true) && $sourceUrl) {
                    $content = $this->rewriteContentImages($content, $sourceUrl, $downloadedMap);
                }

                if (! $isDryRun) {
                    $baseSlug = $slug ?: Str::slug($title);
                    $slug     = $baseSlug;
                    $i = 1;
                    while (News::where('slug', $slug)->exists()) {
                        $slug = $baseSlug . '-' . $i++;
                    }

                    News::create([
                        'title'              => strip_tags($title),
                        'slug'               => $slug,
                        'content'            => $content,
                        'excerpt'            => $excerpt,
                        'status'             => 'published',
                        'published_at'       => $date,
                        'category_id'        => $catId,
                        'user_id'            => 1,
                        'migration_batch_id' => $batch?->id,
                    ]);
                }

                $migratedPosts++;
            }

            $reader->close();

            $batch?->update([
                'status'         => 'completed',
                'migrated_posts' => $migratedPosts,
                'handled_images' => $handledImages,
                'finished_at'    => now(),
                'notes'          => 'Migrasi offline dari XML selesai.',
            ]);

            $label = $isDryRun ? '[DRY RUN] ' : '';
            return [
                'ok'      => true,
                'message' => "{$label}Berhasil memproses {$migratedPosts} post dan {$handledImages} gambar dari XML.",
            ];
        } catch (Throwable $e) {
            $batch?->update(['status' => 'failed', 'notes' => $e->getMessage(), 'finished_at' => now()]);
            Log::error('WordPress offline migration failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    // ─── ROLLBACK ─────────────────────────────────────────────────────────────

    public function rollbackLastMigrationBatch(): array
    {
        $lastBatch = WordpressMigrationBatch::where('status', 'completed')
            ->latest()
            ->first();

        if (! $lastBatch) {
            return ['ok' => false, 'message' => 'Tidak ada batch migrasi yang bisa di-rollback.'];
        }

        $deleted = News::where('migration_batch_id', $lastBatch->id)->count();

        if ($deleted === 0) {
            $lastBatch->update(['status' => 'rolled_back']);
            return ['ok' => false, 'message' => 'Batch ditemukan tapi tidak ada berita yang terhubung.'];
        }

        // Hapus thumbnail dari storage
        News::where('migration_batch_id', $lastBatch->id)
            ->whereNotNull('thumbnail')
            ->each(function (News $news) {
                try {
                    if ($news->thumbnail && Storage::disk('public')->exists($news->thumbnail)) {
                        Storage::disk('public')->delete($news->thumbnail);
                    }
                } catch (Throwable) {
                    // Lanjut meski gagal hapus file
                }
            });

        News::where('migration_batch_id', $lastBatch->id)->delete();
        $lastBatch->update(['status' => 'rolled_back', 'notes' => "Rolled back: {$deleted} post dihapus."]);

        return [
            'ok'      => true,
            'message' => "Rollback berhasil. {$deleted} berita dari batch #{$lastBatch->id} ({$lastBatch->mode}) telah dihapus.",
        ];
    }

    /**
     * Ambil riwayat semua batch migrasi.
     */
    public function getBatchHistory(): \Illuminate\Database\Eloquent\Collection
    {
        return WordpressMigrationBatch::latest()->limit(20)->get();
    }
}
