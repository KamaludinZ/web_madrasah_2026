<?php

namespace Tests\Feature;

use App\Filament\Resources\AchievementResource\Pages\ListAchievements;
use App\Models\Setting;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Toggle "Pengaturan Tampilan Publik" di /admin/achievements untuk
 * menampilkan/menyembunyikan section Ringkasan Prestasi di Beranda.
 */
class AchievementSectionToggleTest extends TestCase
{
    public function test_section_is_shown_by_default(): void
    {
        $this->get('/beranda')->assertOk()->assertSee('id="prestasi-section"', false);
    }

    public function test_admin_can_hide_and_show_section(): void
    {
        $this->actingAs($this->superAdmin());

        Livewire::test(ListAchievements::class)
            ->assertActionExists('publicDisplaySetting')
            ->callAction('publicDisplaySetting', data: ['show_achievements_section' => false])
            ->assertHasNoActionErrors();

        $this->assertFalse((bool) Setting::get('show_achievements_section', true));
        $this->get('/beranda')->assertOk()->assertDontSee('id="prestasi-section"', false);

        Livewire::test(ListAchievements::class)
            ->callAction('publicDisplaySetting', data: ['show_achievements_section' => true]);

        $this->get('/beranda')->assertSee('id="prestasi-section"', false);
    }

    public function test_action_form_shows_current_value(): void
    {
        $this->actingAs($this->superAdmin());
        Setting::set('show_achievements_section', false);

        Livewire::test(ListAchievements::class)
            ->mountAction('publicDisplaySetting')
            ->assertActionDataSet(['show_achievements_section' => false]);
    }

    public function test_hiding_section_keeps_other_beranda_sections(): void
    {
        Setting::set('show_achievements_section', false);

        $this->get('/beranda')
            ->assertOk()
            ->assertSee('hero-slider', false)
            ->assertSee('Berita Sekolah');
    }
}
