<?php

namespace Tests\Feature;

use App\Filament\Pages\ManagePortal;
use App\Models\Setting;
use App\Support\PortalSettings;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Revisi tampilan Halaman Portal: running text paling atas, kop rata tengah
 * dari Setting, tab Portal (kiri, default) & Tentang (kanan), footer ringkas,
 * dan gambar latar dari admin.
 */
class PortalLayoutRevisionTest extends TestCase
{
    public function test_running_text_is_above_the_kop(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertLessThan(strpos($html, '<h1'), strpos($html, 'news-ticker'));
    }

    public function test_kop_shows_logo_name_tagline_then_contacts_from_settings(): void
    {
        Setting::set('logo', 'settings/branding/logo.webp');
        Setting::set('site_name', 'Madrasah Uji');
        Setting::set('site_tagline', 'Tagline Uji');
        Setting::set('top_header_address', 'Jalan Uji 1');
        Setting::set('top_header_email', 'uji@madrasah.test');

        $this->get('/')->assertSeeInOrder(['Logo Madrasah Uji', 'Madrasah Uji', 'Tagline Uji', 'Jalan Uji 1', 'uji@madrasah.test']);
    }

    public function test_portal_tab_is_left_and_opened_by_default(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertLessThan(strpos($html, 'id="tab-tentang"'), strpos($html, 'id="tab-portal"'));
        $this->assertStringContainsString("tab: 'portal'", $html);
    }

    public function test_site_footer_is_replaced_by_portal_footer(): void
    {
        Setting::set('contact_hours', "Senin – Kamis : 07.00 – 15.00 WIB\nJumat : 07.00 – 11.00 WIB");

        $this->get('/')
            ->assertDontSee('Tautan Cepat')
            ->assertSee('Jam Operasional')
            ->assertSee('Jumat : 07.00 – 11.00 WIB')
            ->assertSee('Statistik Pengunjung')
            ->assertSee('aria-label="YouTube"', false)
            ->assertSee('Hak Cipta Dilindungi.')
            ->assertSeeInOrder(['>Beranda</a>', '>Kontak</a>', '>Maklumat</a>', '>Survei</a>'], false);
    }

    public function test_other_pages_keep_the_full_site_footer(): void
    {
        $this->get('/berita')->assertSee('Tautan Cepat')->assertSee('Hak Cipta Dilindungi.');
    }

    public function test_background_image_is_rendered_with_transparency(): void
    {
        $this->get('/')->assertDontSee('background-image: url(', false);

        Setting::set(PortalSettings::BACKGROUND_IMAGE_KEY, 'settings/portal/latar.webp');
        $this->get('/')->assertSee('storage/settings/portal/latar.webp', false)->assertSee('opacity: 0.2;', false);

        Setting::set(PortalSettings::BACKGROUND_TRANSPARENCY_KEY, '50');
        $this->get('/')->assertSee('opacity: 0.5;', false);

        Setting::set(PortalSettings::BACKGROUND_TRANSPARENCY_KEY, '250');
        $this->assertSame(100, PortalSettings::background()['transparency'], 'Nilai di luar 0–100 harus dibatasi.');
    }

    public function test_admin_can_save_portal_settings(): void
    {
        $this->actingAs($this->userByEmail('editor.chief@test.local'));
        $this->get('/admin/manage-portal')->assertOk();

        Livewire::test(ManagePortal::class)
            ->fillForm([PortalSettings::BACKGROUND_TRANSPARENCY_KEY => 70])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(70, PortalSettings::background()['transparency']);

        Livewire::test(ManagePortal::class)
            ->fillForm([PortalSettings::BACKGROUND_TRANSPARENCY_KEY => 150])
            ->call('save')
            ->assertHasFormErrors([PortalSettings::BACKGROUND_TRANSPARENCY_KEY]);
    }

    public function test_settings_page_has_status_section_with_current_value(): void
    {
        $this->actingAs($this->superAdmin());

        $this->get('/admin/manage-portal')->assertOk()->assertSee('Status Portal');
        Livewire::test(ManagePortal::class)->assertFormSet([PortalSettings::KEY => true]);

        PortalSettings::setEnabled(false);
        Livewire::test(ManagePortal::class)->assertFormSet([PortalSettings::KEY => false]);
    }

    public function test_editor_cannot_open_portal_settings(): void
    {
        $this->actingAs($this->userByEmail('editor@test.local'))->get('/admin/manage-portal')->assertForbidden();
    }
}
