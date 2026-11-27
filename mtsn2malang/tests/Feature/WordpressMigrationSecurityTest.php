<?php

namespace Tests\Feature;

use App\Models\News;
use App\Services\WordpressMigrationService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WordpressMigrationSecurityTest extends TestCase
{
    private const SOURCE = 'https://sekolah-lama.sch.id';

    private function png(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
    }

    /** File ekspor WordPress (WXR) minimal berisi 2 post + jebakan keamanan. */
    private function wxr(): string
    {
        $item = fn (string $title, string $slug, string $content) => <<<XML
            <item>
                <title>{$title}</title>
                <content:encoded><![CDATA[{$content}]]></content:encoded>
                <excerpt:encoded><![CDATA[]]></excerpt:encoded>
                <wp:post_name>{$slug}</wp:post_name>
                <wp:post_date>2024-06-10 08:00:00</wp:post_date>
                <wp:status>publish</wp:status>
                <wp:post_type>post</wp:post_type>
                <category domain="category" nicename="berita"><![CDATA[Berita]]></category>
            </item>
            XML;

        $legit = '<p>Upacara</p>'
            . '<img src="https://www.sekolah-lama.sch.id/wp-content/uploads/2024/06/upacara-1024x640.jpg">'
            . '<img src="https://i0.wp.com/www.sekolah-lama.sch.id/wp-content/uploads/2024/06/barisan.png?ssl=1">'
            . '<img src="https://sekolah-lama.sch.id/wp-content/uploads/2024/06/palsu.jpg">'
            . '<img src="http://169.254.169.254/wp-content/uploads/latest/meta-data.jpg">';

        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<!DOCTYPE rss [<!ENTITY xxe SYSTEM "file:///etc/passwd">]>'
            . '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:excerpt="http://wordpress.org/export/1.2/excerpt/" xmlns:wp="http://wordpress.org/export/1.2/" xmlns:dc="http://purl.org/dc/elements/1.1/"><channel>'
            . $item('Upacara Bendera', 'upacara-bendera', $legit)
            . $item('Data Server &xxe;', 'data-server', '<p>Entri jebakan XXE</p>')
            . $item('Vavada', 'vavada', '<p>Как пополнить баланс вавада. Для пополнения баланса используйте карты и электронные кошельки — всё быстро.</p><a href="https://vavada-promocodes25.com/">бонус</a>')
            . '</channel></rss>';
    }

    private function runOffline(bool $dryRun): array
    {
        Storage::disk('local')->put('wordpress-imports/ekspor.xml', $this->wxr());

        return app(WordpressMigrationService::class)->runMigrationOffline([
            'wp_migration_offline_file'           => 'wordpress-imports/ekspor.xml',
            'wp_migration_source_url'             => self::SOURCE,
            'wp_migration_import_images'          => true,
            'wp_migration_rewrite_content_images' => true,
            'wp_migration_dry_run'                => $dryRun,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
        Http::fake([
            'sekolah-lama.sch.id/wp-content/uploads/2024/06/palsu.jpg' => Http::response('<html><script>alert(1)</script></html>', 200),
            'sekolah-lama.sch.id/*' => Http::response($this->png(), 200),
            '*' => Http::response($this->png(), 200),
        ]);
    }

    public function test_offline_migration_imports_all_image_url_variants_and_archives_spam(): void
    {
        $result = $this->runOffline(dryRun: false);
        $this->assertTrue($result['ok']);
        $this->assertStringContainsString('1 post terindikasi spam', $result['message']);

        $news = News::where('slug', 'upacara-bendera')->firstOrFail();
        $this->assertSame('published', $news->status);
        $this->assertStringNotContainsString('wp-content/uploads/2024/06/upacara', $news->content, 'Varian www. ikut dipindah.');
        $this->assertStringNotContainsString('barisan.png', $news->content, 'Varian i0.wp.com ikut dipindah.');
        $this->assertStringContainsString('palsu.jpg', $news->content, 'File bukan gambar tidak disimpan.');
        // Entri berisi entitas eksternal (XXE) dilewati tanpa membaca file server.
        $this->assertFalse(News::where('slug', 'data-server')->exists());
        $this->assertSame(0, News::where('title', 'like', '%root:%')->orWhere('content', 'like', '%root:%')->count());

        // Unduhan hanya ke host sumber; alamat internal tidak pernah dihubungi.
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), '169.254.169.254'));
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'i0.wp.com'));
        Http::assertSent(fn (Request $r) => $r->url() === self::SOURCE . '/wp-content/uploads/2024/06/barisan.png');
        $this->assertCount(2, Storage::disk('public')->allFiles('wordpress-migration'));

        $spam = News::where('slug', 'vavada')->firstOrFail();
        $this->assertSame('archived', $spam->status);
        $this->get(route('berita.show', 'vavada'))->assertNotFound();
    }

    public function test_dry_run_downloads_and_saves_nothing(): void
    {
        $result = $this->runOffline(dryRun: true);

        $this->assertTrue($result['ok']);
        $this->assertSame(0, News::count());
        Http::assertNothingSent();
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_manual_xml_path_cannot_read_other_files(): void
    {
        $this->expectException(\RuntimeException::class);

        app(WordpressMigrationService::class)->runMigrationOffline([
            'wp_migration_offline_file_path' => base_path('.env.example'),
            'wp_migration_dry_run'           => true,
        ]);
    }

    public function test_wordpress_export_upload_is_private(): void
    {
        $source = file_get_contents(app_path('Filament/Pages/ManageWordpressMigration.php'));

        $this->assertMatchesRegularExpression("/FileUpload::make\\('wp_migration_offline_file'\\)(?:(?!FileUpload::make).)*->disk\\('local'\\)/s", $source);
    }
}
