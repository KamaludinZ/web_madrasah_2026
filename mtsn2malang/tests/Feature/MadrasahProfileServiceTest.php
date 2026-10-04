<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\MadrasahProfileService;
use Tests\TestCase;

/**
 * Data profil madrasah untuk Tab Tentang Portal (sumber: Setting).
 */
class MadrasahProfileServiceTest extends TestCase
{
    private function service(): MadrasahProfileService
    {
        return app(MadrasahProfileService::class);
    }

    public function test_empty_profile_returns_empty_values(): void
    {
        $data = $this->service()->forTentangTab();

        $this->assertNull($data['sambutan']['name']);
        $this->assertNull($data['sambutan']['photo']);
        $this->assertSame([], $data['identitas']);
        $this->assertSame([], $data['identitasLinks']);
    }

    public function test_profile_is_read_from_settings(): void
    {
        Setting::set('welcome_headmaster_name', 'Kepala Uji');
        Setting::set('welcome_headmaster_photo', 'kepala/foto.webp');
        Setting::set('visi', 'Visi uji');
        Setting::set('misi', '<ol><li>M</li></ol>');
        Setting::set('tujuan', '<ol><li>T</li></ol>');
        Setting::set('identity_nomenklatur', 'Madrasah Tsanawiyah Negeri 2 Kota Malang');
        Setting::set('identity_npsn', '20581234');
        Setting::set('identity_npsn_url', 'https://referensi.example/npsn');
        Setting::set('identity_nsm_url', 'javascript:alert(1)');

        $data = $this->service()->forTentangTab();

        $this->assertSame('Kepala Uji', $data['sambutan']['name']);
        $this->assertStringEndsWith('/storage/kepala/foto.webp', $data['sambutan']['photo']);
        $this->assertSame('Visi uji', $data['visi']);
        $this->assertSame(['Nomenklatur', 'Jenjang', 'NPSN'], array_keys($data['identitas']));
        $this->assertSame('Madrasah Tsanawiyah (setingkat SMP)', $data['identitas']['Jenjang']);
        $this->assertSame(['Profil NPSN'], array_keys($data['identitasLinks']), 'URL non-http harus dibuang.');
    }

    public function test_every_mapped_field_is_editable_in_admin_profile_form(): void
    {
        $form = file_get_contents(app_path('Filament/Pages/ManageWelcome.php'));

        foreach (MadrasahProfileService::FIELD_MAP as $section => $keys) {
            foreach ($keys as $key) {
                $this->assertMatchesRegularExpression(
                    "/(make|webpFileUpload)\('" . preg_quote($key, '/') . "'/",
                    $form,
                    "Field '{$key}' (bagian {$section}) dibaca Tab Tentang tapi tidak ada di form Sambutan & Profil."
                );
            }
        }
    }

    public function test_every_mapped_field_is_read_by_the_service(): void
    {
        Setting::set('identity_nomenklatur', 'Madrasah Tsanawiyah Negeri 2');
        foreach (MadrasahProfileService::FIELD_MAP as $keys) {
            foreach ($keys as $key) {
                if (! in_array($key, ['identity_nomenklatur', 'welcome_headmaster_photo'], true)) {
                    Setting::set($key, str_ends_with($key, '_url') ? "https://contoh.test/{$key}" : "nilai-{$key}");
                }
            }
        }

        $flat = json_encode($this->service()->forTentangTab());

        foreach (MadrasahProfileService::FIELD_MAP as $keys) {
            foreach ($keys as $key) {
                if (! in_array($key, ['identity_nomenklatur', 'welcome_headmaster_photo'], true)) {
                    $this->assertStringContainsString($key, $flat, "Nilai field '{$key}' tidak muncul di data Tab Tentang.");
                }
            }
        }
    }

    public function test_jenjang_is_derived_from_nomenklatur(): void
    {
        $this->assertSame('Madrasah Aliyah (setingkat SMA)', MadrasahProfileService::jenjangFromNomenklatur('MAN 1 Malang - Madrasah Aliyah Negeri'));
        $this->assertSame('Madrasah Ibtidaiyah (setingkat SD)', MadrasahProfileService::jenjangFromNomenklatur('Madrasah Ibtidaiyah Swasta'));
        $this->assertNull(MadrasahProfileService::jenjangFromNomenklatur('SMP Negeri 1'));
        $this->assertNull(MadrasahProfileService::jenjangFromNomenklatur(null));
    }

    public function test_portal_tentang_tab_renders_profile_in_order(): void
    {
        Setting::set('welcome_headmaster_name', 'Kepala Uji');
        Setting::set('welcome_headmaster_message', '<p>Sambutan</p><script>alert(1)</script>');
        Setting::set('identity_npsn', '20581234');
        Setting::set('visi', 'Visi uji');
        Setting::set('tujuan', '<ol><li>Tujuan uji</li></ol>');

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['id="tentang-sambutan"', 'id="tentang-identitas"', 'id="tentang-visi-misi"', 'id="tentang-tujuan"'], false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_portal_tentang_tab_shows_empty_message_without_profile(): void
    {
        $this->get('/')->assertOk()->assertSee('Profil madrasah belum diisi.');
    }
}
