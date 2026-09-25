<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\ManageSettings;
use App\Filament\Resources\NewsResource\Pages\CreateNews;
use App\Filament\Resources\NewsResource\Pages\EditNews;
use App\Filament\Resources\PageContentResource\Pages\CreatePageContent;
use App\Mail\LoginOtpMail;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\GalleryPhoto;
use App\Models\GalleryPost;
use App\Models\News;
use App\Models\PageContent;
use App\Models\Popup;
use App\Models\PopupItem;
use App\Models\Saran;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    private function category(): Category
    {
        return Category::firstOrCreate(['slug' => 'umum'], ['name' => 'Umum', 'is_active' => true]);
    }

    private function makeNews(array $attributes = []): News
    {
        return News::create(array_merge([
            'user_id' => $this->superAdmin()->id,
            'category_id' => $this->category()->id,
            'title' => 'Berita ' . Str::random(6),
            'content' => '<p>Isi</p>',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ], $attributes));
    }

    // ─── Skrip darurat ───────────────────────────────────────────────────────

    public function test_emergency_scripts_are_removed_from_public_folder(): void
    {
        $this->assertFileDoesNotExist(public_path('db-migrate.php'));
        $this->assertFileDoesNotExist(public_path('artisan-run.php'));
    }

    // ─── XSS ─────────────────────────────────────────────────────────────────

    public function test_editor_cannot_inject_script_into_public_page(): void
    {
        PageContent::where('page_key', 'visi_misi')->delete();
        $this->actingAs($this->userByEmail('editor@test.local'));

        Livewire::test(CreatePageContent::class)
            ->set('data.page_key', 'visi_misi')
            ->set('data.title', 'Visi Misi')
            ->set('data.content', '<p>Visi aman</p><img src=x onerror="alert(document.domain)"><script>alert(1)</script>')
            ->call('create')
            ->assertHasNoErrors();

        Auth::logout();

        $this->get('/tentang/visi-misi')
            ->assertOk()
            ->assertSee('Visi aman')
            ->assertDontSee('onerror', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_news_content_saved_directly_in_database_is_sanitized_on_display(): void
    {
        $news = $this->makeNews([
            'content' => '<p>Paragraf</p><a href="javascript:alert(1)">klik</a><p style="position:fixed;top:0;text-align:center">x</p>',
        ]);

        $this->get('/berita/' . $news->slug)
            ->assertOk()
            ->assertSee('Paragraf')
            ->assertDontSee('javascript:alert', false)
            ->assertDontSee('position:fixed', false);
    }

    public function test_popup_content_is_sanitized_on_every_page(): void
    {
        $popup = Popup::create(['title' => 'Info', 'type' => 'text', 'is_active' => true, 'order' => 1]);
        PopupItem::create(['popup_id' => $popup->id, 'item_type' => 'text', 'content' => '<p>Pengumuman</p><svg onload=alert(1)>', 'order' => 1]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Pengumuman')
            ->assertDontSee('onload=alert', false);
    }

    // ─── Upload SVG ──────────────────────────────────────────────────────────

    public function test_contributor_cannot_upload_svg_with_script(): void
    {
        $this->actingAs($this->userByEmail('contributor@test.local'));
        $title = 'SVG ' . Str::random(6);
        $svg = UploadedFile::fake()->createWithContent(
            'logo.svg',
            '<svg xmlns="http://www.w3.org/2000/svg" xmlns:s="http://www.w3.org/2000/svg"><s:script>alert(1)</s:script></svg>'
        );

        Livewire::test(CreateNews::class)
            ->set('data.title', $title)
            ->set('data.slug', Str::slug($title))
            ->set('data.content', '<p>x</p>')
            ->set('data.status', 'draft')
            ->set('data.category_id', $this->category()->id)
            ->set('data.tags', ['Uji'])
            ->set('data.thumbnail', $svg)
            ->call('create')
            ->assertHasErrors();

        $this->assertDatabaseMissing('news', ['title' => $title]);
    }

    // ─── Login & OTP ─────────────────────────────────────────────────────────

    private function submitLogin(string $email, string $password = self::TEST_PASSWORD)
    {
        $component = Livewire::test(Login::class)
            ->set('data.email', $email)
            ->set('data.password', $password);

        return $component->set('data.captcha', $component->get('captchaCode'))->call('authenticate');
    }

    public function test_admin_login_without_otp_logs_in_directly(): void
    {
        Setting::set('login_otp_enabled', false);

        $this->submitLogin('editor@test.local')->assertHasNoErrors();

        $this->assertAuthenticatedAs($this->userByEmail('editor@test.local'));
    }

    public function test_admin_login_requires_otp_sent_by_email_when_enabled(): void
    {
        Mail::fake();
        Setting::set('login_otp_enabled', true);

        $component = $this->submitLogin('editor@test.local')
            ->assertHasNoErrors()
            ->assertSet('otpStep', true);

        // Password benar saja TIDAK cukup untuk masuk.
        $this->assertGuest();

        $sentOtp = null;
        Mail::assertSent(LoginOtpMail::class, function (LoginOtpMail $mail) use (&$sentOtp) {
            $sentOtp = $mail->otp;
            return $mail->hasTo('editor@test.local');
        });

        $component->set('data.otp', $sentOtp)->call('authenticate')->assertHasNoErrors();

        $this->assertAuthenticatedAs($this->userByEmail('editor@test.local'));
        $this->assertNull($this->userByEmail('editor@test.local')->otp_code, 'OTP harus hangus setelah dipakai.');
    }

    public function test_otp_is_cancelled_after_too_many_wrong_codes(): void
    {
        Mail::fake();
        Setting::set('login_otp_enabled', true);

        $component = $this->submitLogin('editor@test.local')->assertSet('otpStep', true);

        // Kosongkan pembatas percobaan per-menit supaya yang diuji murni
        // batas salah-OTP (keduanya sama-sama aktif di produksi).
        $rateLimitKey = 'livewire-rate-limiter:' . sha1(Login::class . '|authenticate|127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            RateLimiter::clear($rateLimitKey);
            $component->set('data.otp', '000000')->call('authenticate');
        }

        $component->assertSet('otpStep', false);
        $this->assertGuest();
        $this->assertNull($this->userByEmail('editor@test.local')->otp_code);
    }

    public function test_legacy_login_url_redirects_to_admin_login(): void
    {
        $this->get('/login')->assertRedirect('/admin/login');
    }

    // ─── Hak akses berita ────────────────────────────────────────────────────

    public function test_contributor_cannot_publish_directly(): void
    {
        $contributor = $this->userByEmail('contributor@test.local');
        $this->actingAs($contributor);
        $title = 'Kiriman ' . Str::random(6);

        Livewire::test(CreateNews::class)
            ->set('data.title', $title)
            ->set('data.slug', Str::slug($title))
            ->set('data.content', '<p>x</p>')
            ->set('data.status', 'published')
            ->set('data.user_id', $this->superAdmin()->id)
            ->set('data.category_id', $this->category()->id)
            ->set('data.tags', ['Uji'])
            ->call('create');

        $news = News::where('title', $title)->firstOrFail();
        $this->assertSame('review', $news->status);
        $this->assertSame($contributor->id, (int) $news->user_id, 'Kontributor tidak boleh mengganti penulis.');
    }

    public function test_contributor_cannot_edit_own_news_after_published(): void
    {
        $contributor = $this->userByEmail('contributor@test.local');
        $draft = $this->makeNews(['user_id' => $contributor->id, 'status' => 'draft']);
        $published = $this->makeNews(['user_id' => $contributor->id, 'status' => 'published']);

        $this->actingAs($contributor);
        $this->get("/admin/news/{$draft->slug}/edit")->assertOk();
        $this->get("/admin/news/{$published->slug}/edit")->assertForbidden();
    }

    public function test_moderator_can_view_but_not_create_or_edit_news(): void
    {
        $news = $this->makeNews();
        $this->actingAs($this->userByEmail('moderator@test.local'));

        $this->get('/admin/news')->assertOk();
        $this->get('/admin/news/create')->assertForbidden();
        $this->get("/admin/news/{$news->slug}/edit")->assertForbidden();
    }

    public function test_contributor_cannot_delete_gallery_photo_of_other_news(): void
    {
        $foreign = $this->makeNews();
        $post = GalleryPost::create(['news_id' => $foreign->id, 'title' => 'Galeri']);
        $photo = GalleryPhoto::create(['gallery_post_id' => $post->id, 'file_path' => 'gallery/x.webp', 'order' => 0]);

        $contributor = $this->userByEmail('contributor@test.local');
        $own = $this->makeNews(['user_id' => $contributor->id, 'status' => 'draft']);
        $own->syncTags(['Uji']);

        $this->actingAs($contributor);
        Livewire::test(EditNews::class, ['record' => $own->getRouteKey()])
            ->set('data.gallery_photos_to_delete', [$photo->id])
            ->call('save');

        $this->assertNotNull(GalleryPhoto::find($photo->id));
    }

    // ─── Tiket pengaduan ─────────────────────────────────────────────────────

    public function test_ticket_numbers_are_long_random_and_scoped_by_type(): void
    {
        $complaint = Complaint::create(['type' => 'whistleblowing', 'subject' => 'Rahasia', 'content' => 'Isi laporan']);
        $this->assertMatchesRegularExpression('/^ADU-\d{8}-[A-Z2-9]{8}$/', $complaint->ticket_number);

        $saran = Saran::create(['subject' => 'Saran', 'content' => 'Isi saran panjang']);
        $this->assertMatchesRegularExpression('/^SAR-\d{8}-[A-Z2-9]{8}$/', $saran->ticket_number);

        // Tiket whistleblowing tidak boleh terbaca lewat halaman lacak saran.
        $this->get('/survei/saran/lacak?tiket=' . $complaint->ticket_number)
            ->assertOk()
            ->assertDontSee('Rahasia');

        $this->get('/pengaduan/pelacakan?ticket=' . strtolower($complaint->ticket_number))
            ->assertOk()
            ->assertSee('Rahasia');
    }

    // ─── Rahasia di pengaturan ───────────────────────────────────────────────

    public function test_secret_settings_are_encrypted_and_never_sent_to_browser(): void
    {
        $this->actingAs($this->superAdmin());

        $component = Livewire::test(ManageSettings::class)
            ->set('data.mail_password', 'rahasia-smtp')
            ->set('data.gemini_api_key', 'AIza-rahasia')
            ->call('save');

        $raw = Setting::where('key', 'mail_password')->value('value');
        $this->assertNotSame('rahasia-smtp', $raw);
        $this->assertSame('rahasia-smtp', Setting::getSecret('mail_password'));

        // Form tidak pernah berisi rahasia, dan simpan dengan kolom kosong
        // tidak menghapus rahasia yang sudah tersimpan.
        $fresh = Livewire::test(ManageSettings::class);
        $this->assertSame('', $fresh->get('data.mail_password'));
        $this->assertStringNotContainsString('rahasia-smtp', $fresh->html());
        $fresh->call('save');
        $this->assertSame('rahasia-smtp', Setting::getSecret('mail_password'));
        $this->assertSame('AIza-rahasia', Setting::getSecret('gemini_api_key'));
    }
}
