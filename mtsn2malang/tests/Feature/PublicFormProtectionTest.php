<?php

namespace Tests\Feature;

use App\Jobs\SendComplaintNotificationJob;
use App\Models\AppBackup;
use App\Models\Complaint;
use App\Models\Saran;
use App\Support\NumericCaptcha;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Formulir publik (pengaduan, whistleblowing, saran) terlindung dari bot &
 * kiriman ganda, plus pengerasan keamanan lain dari audit November 2026.
 */
class PublicFormProtectionTest extends TestCase
{
    private function complaint(array $overrides = [], ?string $captcha = null)
    {
        return $this->from(route('pengaduan.masyarakat'))->post(route('pengaduan.masyarakat.store'), array_merge([
            'type' => 'masyarakat',
            'name' => 'Wali Murid',
            'subject' => 'Lampu kelas mati',
            'content' => 'Lampu di kelas 8B mati sejak seminggu lalu.',
            'captcha' => $captcha ?? NumericCaptcha::generate('pengaduan'),
        ], $overrides));
    }

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_complaint_forms_show_security_code(): void
    {
        foreach (['pengaduan.masyarakat', 'pengaduan.whistleblowing', 'survei.saran'] as $route) {
            $this->get(route($route))->assertOk()
                ->assertSee('kode-keamanan', false)
                ->assertSee('name="captcha"', false)
                ->assertSee('name="website"', false)
                ->assertSee('data-submit-once', false);
        }
    }

    public function test_complaint_without_or_with_wrong_security_code_is_rejected(): void
    {
        $this->complaint(['captcha' => ''])->assertSessionHasErrors('captcha');
        $this->complaint([], '00000')->assertSessionHasErrors('captcha');

        $this->assertSame(0, Complaint::count());
        Queue::assertNothingPushed();
    }

    public function test_bot_filling_honeypot_gets_fake_success_and_nothing_is_saved(): void
    {
        $this->complaint(['website' => 'https://spam.example'])->assertSessionHas('success');

        $this->assertSame(0, Complaint::count());
        Queue::assertNothingPushed();
    }

    public function test_double_submit_creates_one_complaint_with_same_ticket(): void
    {
        $first = $this->complaint();
        $second = $this->complaint(); // tombol tertekan dua kali

        $this->assertSame(1, Complaint::count());
        Queue::assertPushed(SendComplaintNotificationJob::class, 1);

        $ticket = Complaint::first()->ticket_number;
        $this->assertStringContainsString($ticket, $first->getSession()->get('success'));
        $this->assertStringContainsString($ticket, $second->getSession()->get('success'));

        // Isi berbeda tetap tersimpan sebagai pengaduan baru.
        $this->complaint(['subject' => 'Kran air bocor', 'content' => 'Kran di toilet putra bocor terus.']);
        $this->assertSame(2, Complaint::count());
    }

    public function test_saran_requires_security_code_and_is_not_duplicated(): void
    {
        $payload = fn () => [
            'subject' => 'Kantin', 'content' => 'Mohon tambah menu sayur di kantin.',
            'captcha' => NumericCaptcha::generate('saran'),
        ];

        $this->post(route('survei.saran.store'), array_merge($payload(), ['captcha' => '1']))->assertSessionHasErrors('captcha');
        $this->post(route('survei.saran.store'), $payload())->assertSessionHasNoErrors();
        $this->post(route('survei.saran.store'), $payload())->assertSessionHasNoErrors();

        $this->assertSame(1, Saran::where('type', 'saran')->count());
    }

    public function test_pages_send_content_security_policy(): void
    {
        $response = $this->get(route('home'))->assertOk();

        $csp = $response->headers->get('Content-Security-Policy');
        foreach (["base-uri 'self'", "object-src 'none'", "form-action 'self'", "frame-ancestors 'self'"] as $directive) {
            $this->assertStringContainsString($directive, $csp);
        }
        $response->assertHeader('X-XSS-Protection', '0');
    }

    public function test_inactive_super_admin_cannot_download_backup(): void
    {
        $admin = $this->superAdmin();
        $backup = AppBackup::create([
            'type' => 'manual', 'status' => 'completed', 'filename' => 'tidak-ada.zip',
            'size_bytes' => 1, 'app_version' => '1.0.0', 'started_at' => now(), 'finished_at' => now(),
        ]);
        $url = URL::temporarySignedRoute('backup.download', now()->addMinutes(5), ['backup' => $backup]);

        $admin->forceFill(['is_active' => false])->save();
        $this->actingAs($admin)->get($url)->assertForbidden();

        $admin->forceFill(['is_active' => true])->save();
        $this->actingAs($admin->fresh())->get($url)->assertNotFound(); // lolos cek akses, file memang tidak ada
    }

    public function test_archive_and_brochure_uploads_do_not_accept_svg(): void
    {
        foreach (['ArchiveResource', 'RegistrationResource'] as $resource) {
            $source = file_get_contents(app_path("Filament/Resources/{$resource}.php"));
            $this->assertStringNotContainsString("'image/*'", $source, $resource);
        }
        $this->assertNotContains('svg', config('livewire.temporary_file_upload.preview_mimes'));
    }
}
