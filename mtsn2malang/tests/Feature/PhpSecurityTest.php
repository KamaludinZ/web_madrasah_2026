<?php

namespace Tests\Feature;

use App\Filament\Widgets\PhpSecurityWidget;
use App\Support\PhpSecurity;
use Livewire\Livewire;
use Tests\TestCase;

class PhpSecurityTest extends TestCase
{
    public function test_patched_versions_are_not_vulnerable(): void
    {
        foreach (['8.2.35', '8.3.36', '8.4.27', '8.4.30', '8.5.12', '8.6.0'] as $version) {
            $this->assertFalse(PhpSecurity::isVulnerable($version), "PHP {$version} seharusnya aman");
        }
    }

    public function test_unpatched_and_eol_versions_are_vulnerable(): void
    {
        foreach (['8.4.26', '8.4.19', '8.3.35', '8.2.34', '8.5.11', '8.1.31', '7.4.33'] as $version) {
            $this->assertTrue(PhpSecurity::isVulnerable($version), "PHP {$version} seharusnya rentan");
        }
    }

    public function test_recommendation_points_to_patched_release(): void
    {
        $this->assertStringContainsString('8.4.27', PhpSecurity::recommendation('8.4.19'));
        $this->assertStringContainsString('EOL', PhpSecurity::recommendation('7.4.33'));
    }

    public function test_docker_php_config_disables_session_upload_progress(): void
    {
        $ini = parse_ini_file(base_path('docker/php/php.ini'));
        $this->assertSame('', $ini['session.upload_progress.enabled'] ?? null, 'Off di php.ini dibaca sebagai string kosong');

        $userIni = parse_ini_file(public_path('.user.ini'));
        $this->assertSame('', $userIni['session.upload_progress.enabled'] ?? null);

        $this->assertStringContainsString('php_flag session.upload_progress.enabled off', file_get_contents(public_path('.htaccess')));
    }

    public function test_dockerfile_pins_a_patched_php_version(): void
    {
        preg_match('/^ARG PHP_VERSION=(\S+)$/m', file_get_contents(base_path('Dockerfile')), $m);

        $this->assertNotEmpty($m[1] ?? null);
        $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', $m[1], 'Versi PHP harus dikunci lengkap (major.minor.patch)');
        $this->assertFalse(PhpSecurity::isVulnerable($m[1]));
    }

    public function test_widget_is_only_for_super_admin_and_only_when_needed(): void
    {
        $this->actingAs($this->superAdmin());
        $this->assertSame(
            PhpSecurity::isVulnerable() || PhpSecurity::uploadProgressEnabled(),
            PhpSecurityWidget::canView()
        );

        $this->actingAs($this->userByEmail('editor.chief@test.local'));
        $this->assertFalse(PhpSecurityWidget::canView());
    }

    public function test_widget_renders_warning(): void
    {
        $this->actingAs($this->superAdmin());

        $widget = Livewire::test(PhpSecurityWidget::class)
            ->assertSee('Peringatan keamanan server');

        if (PhpSecurity::isVulnerable()) {
            $widget->assertSee('PHP ' . PHP_VERSION)->assertSee(PhpSecurity::recommendation());
        }
    }
}
