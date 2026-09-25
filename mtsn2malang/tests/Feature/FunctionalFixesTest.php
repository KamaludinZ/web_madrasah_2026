<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageSettings;
use App\Jobs\ApplyManualUpdateJob;
use App\Jobs\RestoreBackupJob;
use App\Mail\NewsPublishedMail;
use App\Models\Archive;
use App\Models\Category;
use App\Models\News;
use App\Models\Setting;
use App\Models\Subscriber;
use App\Services\AiService;
use App\Services\BackupService;
use App\Services\SubscriptionMailerService;
use App\Services\UpdateService;
use App\Support\MailSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;
use ZipArchive;

class FunctionalFixesTest extends TestCase
{
    private function makeNews(array $attributes = []): News
    {
        $category = Category::firstOrCreate(['slug' => 'umum'], ['name' => 'Umum', 'is_active' => true]);

        return News::create(array_merge([
            'user_id' => $this->superAdmin()->id,
            'category_id' => $category->id,
            'title' => 'Berita ' . Str::random(6),
            'content' => '<p>Isi</p>',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ], $attributes));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(storage_path('app/private/tmp-uploads'));

        parent::tearDown();
    }

    // ─── Backup, restore & update ────────────────────────────────────────────

    public function test_uploaded_restore_zip_is_passed_to_job_with_real_path(): void
    {
        Queue::fake();

        Livewire::actingAs($this->superAdmin())
            ->test(ManageSettings::class)
            ->set('restoreFile', UploadedFile::fake()->create('backup.zip', 10, 'application/zip'))
            ->set('restoreConfirmText', 'RESTORE')
            ->call('runRestore');

        Queue::assertPushed(RestoreBackupJob::class, function (RestoreBackupJob $job) {
            return is_file($job->uploadedZipAbsolutePath);
        });
    }

    public function test_uploaded_manual_update_zip_is_passed_to_job_with_real_path(): void
    {
        Queue::fake();

        Livewire::actingAs($this->superAdmin())
            ->test(ManageSettings::class)
            ->set('manualUpdateFile', UploadedFile::fake()->create('update.zip', 10, 'application/zip'))
            ->set('manualUpdateVersion', '9.9.9')
            ->set('manualUpdateConfirm', true)
            ->call('runManualUpdate');

        Queue::assertPushed(ApplyManualUpdateJob::class, function (ApplyManualUpdateJob $job) {
            return is_file($job->uploadedZipAbsolutePath);
        });
    }

    public function test_github_zipball_application_root_is_detected_inside_repo_subfolder(): void
    {
        $tmp = storage_path('app/tmp-test-' . Str::random(6));
        File::ensureDirectoryExists($tmp);
        $zipPath = $tmp . '/release.zip';

        // Struktur zipball GitHub repo ini: <owner>-<repo>-<sha>/mtsn2malang/...
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addFromString('KamaludinZ-web_madrasah_2026-abc123/README.md', 'repo');
        $zip->addFromString('KamaludinZ-web_madrasah_2026-abc123/mtsn2malang/composer.json', '{}');
        $zip->addFromString('KamaludinZ-web_madrasah_2026-abc123/mtsn2malang/artisan', '<?php');
        $zip->addFromString('KamaludinZ-web_madrasah_2026-abc123/mtsn2malang/app/Foo.php', '<?php');
        $zip->close();

        try {
            $method = (new \ReflectionClass(UpdateService::class))->getMethod('extractAndValidate');
            $root = $method->invoke(app(UpdateService::class), $zipPath, $tmp);

            $this->assertStringEndsWith('/mtsn2malang', $root);
            $this->assertFileExists($root . '/app/Foo.php');
        } finally {
            File::deleteDirectory($tmp);
        }
    }

    public function test_backup_does_not_include_operational_tables(): void
    {
        $result = app(BackupService::class)->create();

        try {
            $zip = new ZipArchive();
            $zip->open($result['absolutePath']);
            $sql = $zip->getFromName('database.sql');
            $zip->close();

            $this->assertStringContainsString('CREATE TABLE public.news', $sql);
            $this->assertStringNotContainsString('CREATE TABLE public.app_restores', $sql);
            $this->assertStringNotContainsString('CREATE TABLE public.jobs', $sql);
            $this->assertStringNotContainsString('CREATE TABLE public.sessions', $sql);
        } finally {
            app(BackupService::class)->delete($result['filename']);
        }
    }

    // ─── Error 500 di halaman publik ─────────────────────────────────────────

    public function test_long_user_agent_does_not_break_pages(): void
    {
        $this->withHeader('User-Agent', str_repeat('Mozilla/5.0 ', 50))
            ->get('/')
            ->assertOk();
    }

    public function test_invalid_query_parameters_do_not_cause_server_errors(): void
    {
        foreach ([
            '/ppid/berkala?tahun=abc',
            '/ppid/galeri?bulan=xx',
            '/sakip/renstra?tahun=2024&bulan=13',
            '/pencarian?q[]=a',
            '/pencarian?q=a&from=abc&to=2024-99-99',
            '/pencarian/ajax?q[]=ab',
            '/berita?kategori[]=x',
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_related_news_excludes_current_article(): void
    {
        $news = $this->makeNews();
        $this->makeNews();

        $this->get('/berita/' . $news->slug)
            ->assertOk()
            ->assertViewHas('relatedNews', fn ($related) => $related->isNotEmpty() && ! $related->contains('id', $news->id));
    }

    public function test_inactive_archive_cannot_be_opened_by_id(): void
    {
        $archive = Archive::create([
            'type' => 'berkala', 'title' => 'Arsip Lama', 'published_date' => now(),
            'link_type' => 'link', 'external_link' => 'https://contoh.id/dokumen.pdf', 'is_active' => false,
        ]);

        $this->get("/arsip/{$archive->id}/lihat")->assertNotFound();

        $archive->update(['is_active' => true]);
        $this->get("/arsip/{$archive->id}/lihat")->assertRedirect('https://contoh.id/dokumen.pdf');
    }

    public function test_complaint_form_validates_optional_fields_instead_of_failing(): void
    {
        $this->from('/pengaduan/masyarakat')
            ->post('/pengaduan/masyarakat', [
                'type' => 'masyarakat',
                'name' => str_repeat('a', 300),
                'subject' => 'Subjek',
                'content' => 'Isi pengaduan',
            ])
            ->assertRedirect('/pengaduan/masyarakat')
            ->assertSessionHasErrors('name');
    }

    public function test_user_bio_up_to_500_characters_can_be_saved(): void
    {
        $user = $this->userByEmail('editor@test.local');
        $user->update(['bio' => str_repeat('b', 500)]);

        $this->assertSame(500, mb_strlen($user->fresh()->bio));
    }

    // ─── AI ──────────────────────────────────────────────────────────────────

    public function test_ai_uses_api_key_saved_in_admin_settings(): void
    {
        Setting::set('ai_provider', 'gemini');
        Setting::setSecret('gemini_api_key', 'kunci-dari-panel');

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => "```json\n{\"meta_title\":\"Judul SEO\",\"meta_description\":\"Deskripsi\",\"slug\":\"judul-seo\"}\n```"]]]]],
            ]),
        ]);

        $seo = app(AiService::class)->generateSeo('Judul', 'Konten');

        $this->assertSame('Judul SEO', $seo['meta_title']);
        Http::assertSent(fn ($request) => $request->hasHeader('x-goog-api-key', 'kunci-dari-panel'));
    }

    public function test_ai_reports_failure_when_key_missing(): void
    {
        Setting::set('ai_provider', 'gemini');
        Setting::setSecret('gemini_api_key', null);
        config(['ai.gemini_key' => null]);

        $this->expectException(\RuntimeException::class);
        app(AiService::class)->generateSeo('Judul', 'Konten');
    }

    // ─── Newsletter ──────────────────────────────────────────────────────────

    public function test_newsletter_is_sent_once_per_published_news(): void
    {
        Mail::fake();
        Subscriber::create(['email' => 'pelanggan@contoh.id', 'is_active' => true]);
        $news = $this->makeNews();

        $service = app(SubscriptionMailerService::class);
        $service->queueIfPublished($news);
        $service->queueIfPublished($news->fresh());

        Mail::assertSent(NewsPublishedMail::class, 1);
        $this->assertNotNull($news->fresh()->newsletter_sent_at);
    }

    public function test_newsletter_not_sent_for_drafts(): void
    {
        Mail::fake();
        Subscriber::create(['email' => 'pelanggan@contoh.id', 'is_active' => true]);

        app(SubscriptionMailerService::class)->queueIfPublished($this->makeNews(['status' => 'draft']));

        Mail::assertNothingSent();
    }

    public function test_mail_settings_use_saved_mail_driver(): void
    {
        Setting::set('mail_driver', 'log');
        MailSettings::apply();

        $this->assertSame('log', config('mail.default'));
    }

    // ─── Maintenance ─────────────────────────────────────────────────────────

    public function test_admin_panel_stays_reachable_during_maintenance(): void
    {
        $this->app->maintenanceMode()->activate(['secret' => 'rahasia-uji']);

        try {
            $this->get('/')->assertStatus(503);
            $this->get('/admin/login')->assertOk();
        } finally {
            $this->app->maintenanceMode()->deactivate();
        }
    }
}
