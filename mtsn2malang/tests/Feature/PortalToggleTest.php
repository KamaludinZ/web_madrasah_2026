<?php

namespace Tests\Feature;

use App\Models\PortalCategory;
use App\Support\PortalSettings;
use Tests\TestCase;

/**
 * Saklar aktif/nonaktif Halaman Portal (Setting portal_enabled).
 */
class PortalToggleTest extends TestCase
{
    public function test_portal_is_enabled_by_default(): void
    {
        $this->assertTrue(PortalSettings::enabled());
        $this->get('/')->assertOk()->assertSee('id="tab-portal"', false);
    }

    public function test_disabled_portal_shows_beranda_at_root_without_redirect(): void
    {
        PortalSettings::setEnabled(false);

        $this->get('/')
            ->assertOk()
            ->assertSee('hero-slider', false)
            ->assertDontSee('id="tab-portal"', false);
    }

    public function test_disabling_portal_keeps_its_data(): void
    {
        PortalCategory::create(['name' => 'Tetap Ada']);

        PortalSettings::setEnabled(false);
        PortalSettings::setEnabled(true);

        $this->assertDatabaseHas('portal_categories', ['name' => 'Tetap Ada']);
        $this->get('/')->assertSee('Tetap Ada');
    }

    public function test_header_hides_portal_menu_when_disabled(): void
    {
        $this->get('/berita')->assertSee('>Portal</a>', false);

        PortalSettings::setEnabled(false);

        $this->get('/berita')
            ->assertDontSee('>Portal</a>', false)
            ->assertSee('Beranda');
    }

    public function test_admin_switch_turns_portal_off_and_on(): void
    {
        $this->actingAs($this->superAdmin());

        \Livewire\Livewire::test(\App\Filament\Pages\ManagePortal::class)
            ->fillForm([PortalSettings::KEY => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse(PortalSettings::enabled());
        $this->get('/')->assertSee('hero-slider', false)->assertDontSee('id="tab-portal"', false);

        \Livewire\Livewire::test(\App\Filament\Pages\ManagePortal::class)
            ->fillForm([PortalSettings::KEY => true])
            ->call('save');

        $this->assertTrue(PortalSettings::enabled());
        $this->get('/')->assertSee('id="tab-portal"', false);
    }

    public function test_toggling_portal_is_written_to_audit_log_only_when_changed(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin);
        $log = fn () => \Spatie\Activitylog\Models\Activity::where('description', 'like', 'Halaman Portal %');

        // Simpan tanpa mengubah status → tidak dicatat.
        \Livewire\Livewire::test(\App\Filament\Pages\ManagePortal::class)->call('save');
        $this->assertSame(0, $log()->count());

        \Livewire\Livewire::test(\App\Filament\Pages\ManagePortal::class)
            ->fillForm([PortalSettings::KEY => false])->call('save');

        $entry = $log()->latest('id')->first();
        $this->assertSame('Halaman Portal dinonaktifkan', $entry->description);
        $this->assertSame($admin->id, $entry->causer_id);
    }

    public function test_saving_switch_keeps_background_settings(): void
    {
        $this->actingAs($this->superAdmin());
        \App\Models\Setting::set(PortalSettings::BACKGROUND_TRANSPARENCY_KEY, '60');

        \Livewire\Livewire::test(\App\Filament\Pages\ManagePortal::class)
            ->fillForm([PortalSettings::KEY => false])
            ->call('save');

        $this->assertSame(60, PortalSettings::background()['transparency']);
    }

    public function test_settings_page_shows_saved_status_badge(): void
    {
        $this->actingAs($this->superAdmin());

        $this->get('/admin/manage-portal')->assertSee('Portal Menyala')->assertDontSee('Portal Mati');

        PortalSettings::setEnabled(false);

        $this->get('/admin/manage-portal')->assertSee('Portal Mati')->assertSee('menampilkan Beranda');
    }

    public function test_sidebar_badge_only_when_portal_is_off(): void
    {
        $this->assertNull(\App\Filament\Pages\ManagePortal::getNavigationBadge());

        PortalSettings::setEnabled(false);

        $this->assertSame('Mati', \App\Filament\Pages\ManagePortal::getNavigationBadge());
    }

    public function test_sitemap_drops_duplicate_beranda_when_portal_is_off(): void
    {
        $beranda = '<loc>' . route('home') . '</loc>';
        $root = '<loc>' . route('portal') . '</loc>';

        $this->get('/sitemap.xml')->assertSee($root, false)->assertSee($beranda, false);

        // Cache sitemap harus ikut dibersihkan saat saklar diubah.
        PortalSettings::setEnabled(false);

        $this->get('/sitemap.xml')->assertSee($root, false)->assertDontSee($beranda, false);
    }

    public function test_setting_value_is_stored_as_boolean_string(): void
    {
        PortalSettings::setEnabled(false);
        $this->assertDatabaseHas('settings', ['key' => PortalSettings::KEY, 'value' => '0']);
        $this->assertFalse(PortalSettings::enabled());

        PortalSettings::setEnabled(true);
        $this->assertTrue(PortalSettings::enabled());
    }
}
