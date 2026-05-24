<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\News;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportWordPress extends Command
{
    protected $signature = 'wordpress:import
                            {--host=localhost : WordPress DB host}
                            {--port=3306 : WordPress DB port}
                            {--database= : WordPress database name}
                            {--username= : WordPress DB username}
                            {--password= : WordPress DB password}
                            {--prefix=wp_ : WordPress table prefix}
                            {--limit=0 : Limit number of posts (0 = all)}';

    protected $description = 'Import berita, kategori, dan tag dari database WordPress lama';

    public function handle(): int
    {
        $this->info('🚀 Memulai migrasi data dari WordPress...');

        // Connect to WordPress database
        config(['database.connections.wordpress' => [
            'driver' => 'mysql',
            'host' => $this->option('host'),
            'port' => $this->option('port'),
            'database' => $this->option('database'),
            'username' => $this->option('username'),
            'password' => $this->option('password'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ]]);

        $prefix = $this->option('prefix');
        $limit = (int) $this->option('limit');

        try {
            // Test connection
            DB::connection('wordpress')->getPdo();
            $this->info('✅ Koneksi ke database WordPress berhasil!');
        } catch (\Exception $e) {
            $this->error('❌ Gagal terhubung ke database WordPress: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // Import Categories
        $this->info('📁 Mengimpor kategori...');
        $this->importCategories($prefix);

        // Import Posts
        $this->info('📰 Mengimpor berita...');
        $this->importPosts($prefix, $limit);

        $this->info('✅ Migrasi selesai!');
        return Command::SUCCESS;
    }

    private function importCategories(string $prefix): void
    {
        $wpCategories = DB::connection('wordpress')
            ->table("{$prefix}terms as t")
            ->join("{$prefix}term_taxonomy as tt", 't.term_id', '=', 'tt.term_id')
            ->where('tt.taxonomy', 'category')
            ->select('t.name', 't.slug', 'tt.description')
            ->get();

        $imported = 0;
        foreach ($wpCategories as $cat) {
            if ($cat->slug === 'uncategorized') continue;

            Category::firstOrCreate(
                ['slug' => $cat->slug],
                [
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'description' => $cat->description,
                    'is_active' => true,
                ]
            );
            $imported++;
        }

        $this->info("  ✅ {$imported} kategori diimpor");
    }

    private function importPosts(string $prefix, int $limit): void
    {
        $defaultUser = User::first();

        $query = DB::connection('wordpress')
            ->table("{$prefix}posts")
            ->where('post_type', 'post')
            ->where('post_status', 'publish')
            ->orderBy('post_date', 'desc');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $posts = $query->get();
        $imported = 0;
        $skipped = 0;

        $this->output->progressStart($posts->count());

        foreach ($posts as $post) {
            // Skip if already imported
            if (News::where('slug', $post->post_name)->exists()) {
                $skipped++;
                $this->output->progressAdvance();
                continue;
            }

            // Get category
            $categoryId = null;
            $wpCategory = DB::connection('wordpress')
                ->table("{$prefix}term_relationships as tr")
                ->join("{$prefix}term_taxonomy as tt", 'tr.term_taxonomy_id', '=', 'tt.term_taxonomy_id')
                ->join("{$prefix}terms as t", 'tt.term_id', '=', 't.term_id')
                ->where('tr.object_id', $post->ID)
                ->where('tt.taxonomy', 'category')
                ->first();

            if ($wpCategory) {
                $category = Category::where('slug', $wpCategory->slug)->first();
                $categoryId = $category?->id;
            }

            // Get tags
            $wpTags = DB::connection('wordpress')
                ->table("{$prefix}term_relationships as tr")
                ->join("{$prefix}term_taxonomy as tt", 'tr.term_taxonomy_id', '=', 'tt.term_taxonomy_id')
                ->join("{$prefix}terms as t", 'tt.term_id', '=', 't.term_id')
                ->where('tr.object_id', $post->ID)
                ->where('tt.taxonomy', 'post_tag')
                ->pluck('t.name')
                ->toArray();

            // Get thumbnail
            $thumbnail = null;
            $thumbnailMeta = DB::connection('wordpress')
                ->table("{$prefix}postmeta")
                ->where('post_id', $post->ID)
                ->where('meta_key', '_thumbnail_id')
                ->first();

            if ($thumbnailMeta) {
                $attachmentMeta = DB::connection('wordpress')
                    ->table("{$prefix}postmeta")
                    ->where('post_id', $thumbnailMeta->meta_value)
                    ->where('meta_key', '_wp_attached_file')
                    ->first();
                $thumbnail = $attachmentMeta?->meta_value;
            }

            // Create news
            $news = News::create([
                'user_id' => $defaultUser->id,
                'category_id' => $categoryId,
                'title' => $post->post_title,
                'slug' => $post->post_name ?: Str::slug($post->post_title),
                'excerpt' => $post->post_excerpt ?: Str::limit(strip_tags($post->post_content), 200),
                'content' => $post->post_content,
                'thumbnail' => $thumbnail,
                'status' => 'published',
                'published_at' => $post->post_date,
                'view_count' => 0,
            ]);

            // Attach tags
            if (!empty($wpTags)) {
                $news->attachTags($wpTags);
            }

            $imported++;
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->info("  ✅ {$imported} berita diimpor, {$skipped} dilewati (sudah ada)");
    }
}
