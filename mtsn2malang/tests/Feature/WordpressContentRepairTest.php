<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\News;
use App\Services\WordpressMediaRepairService;
use App\Support\ContentSpamDetector;
use App\Support\WordpressContent;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WordpressContentRepairTest extends TestCase
{
    private const HOST = 'mtsn2kotamalang.sch.id';

    /** PNG 1x1 valid. */
    private function png(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
    }

    private function makeNews(string $content, array $attributes = []): News
    {
        return News::create(array_merge([
            'title' => 'Berita Migrasi ' . uniqid(), 'slug' => 'berita-migrasi-' . uniqid(), 'content' => $content,
            'category_id' => Category::first()->id, 'user_id' => $this->superAdmin()->id,
            'status' => 'published', 'published_at' => now()->subDay(),
        ], $attributes));
    }

    // ─── Shortcode WordPress ────────────────────────────────────────────────

    public function test_shortcodes_are_rendered_or_removed(): void
    {
        $html = WordpressContent::normalize(
            '<p>[embed]https://www.youtube.com/watch?v=dQw4w9WgXcQ[/embed]</p>'
            . '<p>[embed]https://www.instagram.com/p/DKbAlx2SITB/?utm_source=ig_web_copy_link[/embed]</p>'
            . '[caption id="attachment_5" align="aligncenter" width="300"]<img src="/a.jpg" alt="x"> Upacara bendera[/caption]'
            . '<p>[gallery size="medium" ids="9754,9756"]</p><p>Isi tetap ada</p>[vc_row]teks builder[/vc_row]'
        );

        $this->assertStringContainsString('<iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ"', $html);
        $this->assertStringContainsString('https://www.instagram.com/p/DKbAlx2SITB/embed', $html);
        $this->assertStringContainsString('<figure><img src="/a.jpg" alt="x"><figcaption>Upacara bendera</figcaption></figure>', $html);
        $this->assertStringNotContainsString('[gallery', $html);
        $this->assertStringNotContainsString('[vc_row', $html);
        $this->assertStringContainsString('teks builder', $html);
        $this->assertStringContainsString('<p>Isi tetap ada</p>', $html);
    }

    public function test_embed_of_unknown_or_unsafe_url_becomes_plain_link_or_nothing(): void
    {
        $this->assertStringContainsString('<a href="https://contoh.go.id/x"', WordpressContent::normalize('[embed]https://contoh.go.id/x[/embed]'));
        $this->assertStringNotContainsString('<iframe', WordpressContent::normalize('[embed]https://evil.example/embed[/embed]'));
    }

    public function test_news_page_and_excerpt_hide_shortcodes(): void
    {
        $news = $this->makeNews('<p>[gallery ids="39,38"]</p><p>Kegiatan pramuka [Foto] berjalan lancar.</p>');

        $this->get(route('berita.show', $news->slug))->assertOk()
            ->assertDontSee('[gallery', false)
            ->assertSee('Kegiatan pramuka [Foto] berjalan lancar.');

        $this->assertSame('Kegiatan pramuka [Foto] berjalan lancar.', $news->excerpt);
    }

    // ─── Deteksi spam ───────────────────────────────────────────────────────

    public function test_spam_detector_flags_injected_casino_post_but_not_legit_articles(): void
    {
        $spam = ContentSpamDetector::reasons('Vavada', '<p>Как пополнить баланс вавада Для пополнения баланса используйте удобные методы: банковские карты, электронные кошельки.</p><a href="https://vavada-promocodes25.com/">бонус</a>');
        $this->assertCount(2, $spam);

        $hidden = ContentSpamDetector::reasons('Kegiatan', '<p>Kegiatan siswa.</p><div style="display:none"><a href="https://contoh.com">x</a></div>');
        $this->assertNotEmpty($hidden);

        $this->assertSame([], ContentSpamDetector::reasons('Penyuluhan bahaya judi online dan narkoba', '<p>Siswa diajak menjauhi judi online, slot, dan narkoba. بسم الله الرحمن الرحيم</p><a href="https://jatim.kemenag.go.id">Kemenag</a>'));
    }

    public function test_audit_command_archives_spam_only_when_asked(): void
    {
        $spam = $this->makeNews('<p>Как пополнить баланс вавада. Для пополнения баланса используйте карты и кошельки, всё очень быстро.</p>', ['title' => 'Vavada']);
        $legit = $this->makeNews('<p>Upacara bendera berjalan khidmat.</p>');

        $this->artisan('wordpress:audit-content')->assertSuccessful();
        $this->assertSame('published', $spam->fresh()->status);

        $this->artisan('wordpress:audit-content --unpublish')->assertSuccessful();
        $this->assertSame('archived', $spam->fresh()->status);
        $this->assertSame('published', $legit->fresh()->status);
        $this->get(route('berita.show', $spam->slug))->assertNotFound();
    }

    // ─── Pemulihan media wp-content ─────────────────────────────────────────

    public function test_repair_media_from_backup_folder_rewrites_all_url_variants(): void
    {
        Storage::fake('public');
        $backup = storage_path('framework/testing/wp-backup');
        File::ensureDirectoryExists($backup . '/2024/06');
        File::put($backup . '/2024/06/foto.jpg', $this->png());         // nama asli (tanpa -1024x640)
        File::put($backup . '/2024/06/surat.pdf', "%PDF-1.4\n...");

        $news = $this->makeNews(
            '<p><img src="https://i0.wp.com/www.' . self::HOST . '/wp-content/uploads/2024/06/foto-1024x640.jpg?ssl=1" srcset="https://www.' . self::HOST . '/wp-content/uploads/2024/06/foto-300x200.jpg 300w" sizes="(max-width: 600px)"></p>'
            . '<p><a href="https://' . self::HOST . '/wp-content/uploads/2024/06/surat.pdf">Unduh surat</a></p>'
            . '<p><img src="https://www.' . self::HOST . '/wp-content/uploads/2024/06/hilang.jpg"></p>'
        );
        $updatedAt = $news->fresh()->updated_at;

        $this->artisan('wordpress:repair-media', ['--from' => $backup, '--host' => [self::HOST], '--dry-run' => true])->assertSuccessful();
        $this->assertSame($news->content, $news->fresh()->content, 'Dry-run tidak mengubah apa pun.');

        $this->artisan('wordpress:repair-media', ['--from' => $backup, '--host' => [self::HOST], '--remove-missing' => true])->assertSuccessful();

        $content = $news->fresh()->content;
        $this->assertStringContainsString('src="/storage/wordpress-media/uploads/2024/06/foto-1024x640.jpg"', $content);
        $this->assertStringContainsString('href="/storage/wordpress-media/uploads/2024/06/surat.pdf"', $content);
        $this->assertStringNotContainsString('srcset', $content);
        $this->assertStringNotContainsString('hilang.jpg', $content, '--remove-missing membuang gambar yang tetap tidak ada.');
        Storage::disk('public')->assertExists('wordpress-media/uploads/2024/06/foto-1024x640.jpg');
        $this->assertTrue($updatedAt->equalTo($news->fresh()->updated_at));

        File::deleteDirectory($backup);
    }

    public function test_repair_media_rejects_disguised_files_and_path_traversal(): void
    {
        Storage::fake('public');
        $backup = storage_path('framework/testing/wp-backup-evil');
        File::ensureDirectoryExists($backup . '/2024/06');
        File::put($backup . '/2024/06/shell.jpg', '<?php system($_GET["c"]); ?>');
        File::put($backup . '/2024/06/page.pdf', '<html><script>alert(1)</script></html>');

        $service = new WordpressMediaRepairService([self::HOST], $backup);

        $this->assertNull($service->resolve('wp-content/uploads/2024/06/shell.jpg'));
        $this->assertNull($service->resolve('wp-content/uploads/2024/06/page.pdf'));
        $this->assertNull($service->resolve('wp-content/uploads/../../../.env.jpg'));
        $this->assertNull($service->resolve('wp-content/uploads/2024/06/backdoor.php'));
        $this->assertSame([], Storage::disk('public')->allFiles());

        File::deleteDirectory($backup);
    }

    public function test_repair_media_downloads_only_from_fixed_public_copies(): void
    {
        Storage::fake('public');
        Http::fake([
            'i0.wp.com/*' => Http::response('', 404),
            'web.archive.org/*' => Http::response($this->png(), 200),
        ]);

        $service = new WordpressMediaRepairService([self::HOST], null, true);
        $url = $service->resolve('wp-content/uploads/2025/09/image-8.png');

        $this->assertSame('/storage/wordpress-media/uploads/2025/09/image-8.png', $url);
        Http::assertSent(fn (Request $r) => str_starts_with($r->url(), 'https://i0.wp.com/www.' . self::HOST . '/wp-content/uploads/2025/09/image-8.png'));
        Http::assertSent(fn (Request $r) => str_starts_with($r->url(), 'https://web.archive.org/web/2026id_/https://www.' . self::HOST . '/'));
        Http::assertSentCount(2);
    }
}
