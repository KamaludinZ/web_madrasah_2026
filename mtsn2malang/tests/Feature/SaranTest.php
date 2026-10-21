<?php

namespace Tests\Feature;

use App\Filament\Resources\SaranResource;
use App\Filament\Resources\SaranResource\Pages\EditSaran;
use App\Models\Saran;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class SaranTest extends TestCase
{
    private function makeSaran(array $attributes = []): Saran
    {
        return Saran::create(array_merge([
            'name'    => 'Siti',
            'email'   => 'siti@test.local',
            'phone'   => '085755512345',
            'subject' => 'Perpustakaan',
            'content' => 'Mohon jam buka perpustakaan diperpanjang.',
            'status'  => 'pending',
        ], $attributes));
    }

    public function test_saran_form_records_phone_number(): void
    {
        Queue::fake();

        $this->get(route('survei.saran'))->assertOk()->assertSee('name="phone"', false);

        $this->post(route('survei.saran.store'), [
            'name'    => 'Siti',
            'phone'   => ' 0812-3456-7890 ',
            'subject' => 'Kantin',
            'content' => 'Mohon menu kantin lebih bervariasi.',
        ])->assertRedirect(route('survei.saran'))->assertSessionHasNoErrors();

        $this->assertSame('0812-3456-7890', Saran::where('type', 'saran')->latest('id')->value('phone'));
    }

    public function test_saran_phone_is_optional_but_must_be_valid(): void
    {
        Queue::fake();

        $this->post(route('survei.saran.store'), [
            'subject' => 'Tanpa telepon',
            'content' => 'Saran tanpa nomor telepon tetap diterima.',
        ])->assertSessionHasNoErrors();
        $this->assertNull(Saran::where('type', 'saran')->latest('id')->value('phone'));

        $this->post(route('survei.saran.store'), [
            'phone'   => 'bukan-nomor',
            'subject' => 'Telepon salah',
            'content' => 'Nomor telepon tidak valid harus ditolak.',
        ])->assertSessionHasErrors('phone');
    }

    public function test_tracking_shows_response_from_admin_but_not_phone(): void
    {
        $saran = $this->makeSaran([
            'status'       => 'resolved',
            'response'     => "Terima kasih, jam buka diperpanjang sampai 16.00.\nBerlaku mulai bulan depan.",
            'responded_at' => now(),
        ]);

        $this->get(route('survei.saran.lacak', ['tiket' => $saran->ticket_number]))
            ->assertOk()
            ->assertSee('Tanggapan dari Madrasah')
            ->assertSee('jam buka diperpanjang sampai 16.00.')
            ->assertSee('Berlaku mulai bulan depan.')
            ->assertDontSee('085755512345');
    }

    public function test_tracking_without_response_hides_response_box(): void
    {
        $saran = $this->makeSaran();

        $this->get(route('survei.saran.lacak', ['tiket' => $saran->ticket_number]))
            ->assertOk()
            ->assertSee($saran->ticket_number)
            ->assertDontSee('Tanggapan dari Madrasah');
    }

    public function test_admin_detail_shows_phone_and_saves_response_for_tracking(): void
    {
        Queue::fake();
        $saran = $this->makeSaran();

        Livewire::actingAs($this->superAdmin())
            ->test(EditSaran::class, ['record' => $saran->getRouteKey()])
            ->assertFormSet(['phone' => '085755512345'])
            ->assertSee('Tanggapan untuk Pengirim')
            ->fillForm(['status' => 'resolved', 'response' => 'Sudah kami tindak lanjuti.'])
            ->call('save')
            ->assertHasNoFormErrors();

        $saran->refresh();
        $this->assertSame('Sudah kami tindak lanjuti.', $saran->response);
        $this->assertNotNull($saran->responded_at, 'Tanggal tindak lanjut terisi otomatis.');

        $this->get(route('survei.saran.lacak', ['tiket' => $saran->ticket_number]))
            ->assertSee('Sudah kami tindak lanjuti.');
    }

    public function test_whatsapp_number_is_normalized_for_wa_link(): void
    {
        $this->assertSame('6281234567890', SaranResource::whatsappNumber('0812-3456-7890'));
        $this->assertSame('6281234567890', SaranResource::whatsappNumber('+62 812 3456 7890'));
        $this->assertNull(SaranResource::whatsappNumber(null));
    }
}
