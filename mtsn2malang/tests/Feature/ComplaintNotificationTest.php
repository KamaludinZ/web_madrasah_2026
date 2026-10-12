<?php

namespace Tests\Feature;

use App\Filament\Resources\ComplaintResource\Pages\EditComplaint;
use App\Jobs\SendComplaintNotificationJob;
use App\Models\Complaint;
use App\Models\Saran;
use App\Models\Setting;
use App\Services\ComplaintNotifier;
use App\Support\WhatsappGateway;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class ComplaintNotificationTest extends TestCase
{
    private function enableNotifications(string $provider = 'fonnte'): void
    {
        Setting::set('mail_driver', 'array');
        Setting::set('mail_from_address', 'noreply@test.local');
        Setting::set('notif_email_enabled', true);
        Setting::set('notif_whatsapp_enabled', true);
        Setting::set('notif_reporter_enabled', true);
        Setting::set('notif_officer_emails', "petugas1@test.local\npetugas2@test.local");
        Setting::set('notif_officer_whatsapp', '081111111111');
        Setting::set('wa_provider', $provider);
        Setting::setSecret('wa_fonnte_token', 'fonnte-token');
        Setting::setSecret('wa_balesotomatis_api_key', 'bales-key');
        Setting::set('wa_balesotomatis_number_id', '42');
    }

    /** @return string[] alamat tujuan email yang terkirim lewat mailer array */
    private function sentEmailRecipients(): array
    {
        return collect(Mail::mailer('array')->getSymfonyTransport()->messages())
            ->flatMap(fn ($sent) => collect($sent->getOriginalMessage()->getTo())->map->getAddress())
            ->all();
    }

    private function makeComplaint(array $attributes = []): Complaint
    {
        return Complaint::create(array_merge([
            'type'    => 'masyarakat',
            'name'    => 'Budi',
            'email'   => 'budi@test.local',
            'phone'   => '082222222222',
            'subject' => 'Lampu kelas mati',
            'content' => 'Lampu kelas 7A mati sejak seminggu.',
        ], $attributes));
    }

    public function test_submitting_complaint_and_saran_dispatches_notification_job(): void
    {
        Queue::fake();

        $this->post(route('pengaduan.masyarakat.store'), [
            'type' => 'masyarakat', 'subject' => 'Tes', 'content' => 'Isi pengaduan tes',
        ])->assertSessionHas('success');

        $this->post(route('survei.saran.store'), [
            'subject' => 'Tes saran', 'content' => 'Isi saran yang cukup panjang',
        ])->assertRedirect();

        Queue::assertPushed(SendComplaintNotificationJob::class, 2);
        Queue::assertPushed(SendComplaintNotificationJob::class, fn ($job) => $job->event === ComplaintNotifier::EVENT_CREATED);
    }

    public function test_new_complaint_notifies_officers_and_reporter_via_email_and_fonnte(): void
    {
        $this->enableNotifications('fonnte');
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'detail' => 'success! message in queue'])]);

        $complaint = $this->makeComplaint();
        app(ComplaintNotifier::class)->handle($complaint, ComplaintNotifier::EVENT_CREATED);

        $this->assertEqualsCanonicalizing(
            ['petugas1@test.local', 'petugas2@test.local', 'budi@test.local'],
            $this->sentEmailRecipients()
        );

        Http::assertSentCount(2);
        Http::assertSent(fn (Request $r) => $r['target'] === '6281111111111'
            && $r->hasHeader('Authorization', 'fonnte-token')
            && str_contains($r['message'], $complaint->ticket_number));
        Http::assertSent(fn (Request $r) => $r['target'] === '6282222222222'
            && str_contains($r['message'], 'Budi'));
    }

    public function test_new_saran_only_notifies_officers_via_balesotomatis(): void
    {
        $this->enableNotifications('balesotomatis');
        Http::fake(['api.balesotomatis.id/*' => Http::response(['status' => true])]);

        $saran = Saran::create([
            'name' => 'Ani', 'email' => 'ani@test.local', 'subject' => 'Kantin', 'content' => 'Tambah menu sehat',
        ]);
        app(ComplaintNotifier::class)->handle($saran, ComplaintNotifier::EVENT_CREATED);

        $this->assertEqualsCanonicalizing(['petugas1@test.local', 'petugas2@test.local'], $this->sentEmailRecipients());

        Http::assertSentCount(1);
        Http::assertSent(fn (Request $r) => $r->url() === 'https://api.balesotomatis.id/public/v1/send_personal_message'
            && $r['api_key'] === 'bales-key'
            && $r['number_id'] === '42'
            && $r['phone_no'] === '6281111111111'
            && str_contains($r['message'], 'Saran Baru'));
    }

    public function test_status_update_from_admin_notifies_reporter_only(): void
    {
        $this->enableNotifications('fonnte');
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true])]);
        Setting::set('notif_tpl_reporter_update_wa', 'Tiket {tiket} kini {status}: {tanggapan}');

        $complaint = $this->makeComplaint();

        Livewire::actingAs($this->superAdmin())
            ->test(EditComplaint::class, ['record' => $complaint->getRouteKey()])
            ->fillForm(['status' => 'resolved', 'response' => 'Lampu sudah diganti.'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(['budi@test.local'], $this->sentEmailRecipients());
        Http::assertSentCount(1);
        Http::assertSent(fn (Request $r) => $r['target'] === '6282222222222'
            && $r['message'] === "Tiket {$complaint->ticket_number} kini Selesai: Lampu sudah diganti.");
    }

    public function test_nothing_is_sent_while_channels_are_disabled(): void
    {
        Setting::set('mail_driver', 'array');
        Http::fake();

        app(ComplaintNotifier::class)->handle($this->makeComplaint(), ComplaintNotifier::EVENT_CREATED);

        $this->assertSame([], $this->sentEmailRecipients());
        Http::assertNothingSent();
    }

    public function test_gateway_failure_is_logged_without_breaking_other_recipients(): void
    {
        $this->enableNotifications('fonnte');
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => false, 'reason' => 'token invalid'])]);

        app(ComplaintNotifier::class)->handle($this->makeComplaint(), ComplaintNotifier::EVENT_CREATED);

        // WA gagal semua, email tetap terkirim ke petugas + pelapor.
        $this->assertCount(3, $this->sentEmailRecipients());
        Http::assertSentCount(2);
    }

    public function test_phone_numbers_are_normalized(): void
    {
        $this->assertSame('6281234567890', WhatsappGateway::normalizeNumber('0812-3456-7890'));
        $this->assertSame('6281234567890', WhatsappGateway::normalizeNumber('+62 812 3456 7890'));
        $this->assertSame('6281234567890', WhatsappGateway::normalizeNumber('81234567890'));
        $this->assertNull(WhatsappGateway::normalizeNumber('12345'));
    }
}
