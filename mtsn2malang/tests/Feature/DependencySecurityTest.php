<?php

namespace Tests\Feature;

use Composer\InstalledVersions;
use Tests\TestCase;

/**
 * Penjaga agar paket dengan celah keamanan yang sudah ditambal tidak
 * kembali ke versi rentan (mis. composer.lock lama ter-commit ulang).
 */
class DependencySecurityTest extends TestCase
{
    /** Versi minimum aman (sumber: composer audit / pemindai Coolify, Nov 2026). */
    private const MINIMUM_VERSIONS = [
        'league/commonmark'           => '2.10.2', // DoS Markdown (CVE-2026-71488, GHSA-3q6v-r5mr-hxv8, GHSA-8rr7-cvq3-gmfh)
        'laravel/framework'           => '12.74.0',
        'filament/forms'              => '3.3.53', // XSS RichEditor (CVE-2026-55409)
        'livewire/livewire'           => '3.8.1',
        'guzzlehttp/guzzle'           => '7.15.2',
        'guzzlehttp/psr7'             => '2.12.3',
        'mtdowling/jmespath.php'      => '2.9.0',
        'spatie/laravel-medialibrary' => '11.23.0',
        'ueberdosis/tiptap-php'       => '2.2.0',
    ];

    public function test_patched_package_versions_are_installed(): void
    {
        foreach (self::MINIMUM_VERSIONS as $package => $minimum) {
            $installed = ltrim((string) InstalledVersions::getPrettyVersion($package), 'v');

            $this->assertTrue(
                version_compare($installed, $minimum, '>='),
                "{$package} {$installed} masih rentan — minimal {$minimum}."
            );
        }
    }

    public function test_vulnerable_tinymce_media_plugin_is_disabled(): void
    {
        // TinyMCE 7.9.3 (terakhir untuk plugin Filament v3) rentan XSS lewat
        // plugin "media" (CVE-2026-116675).
        foreach (config('filament-tinyeditor.profiles') as $name => $profile) {
            $this->assertNotContains('media', preg_split('/\s+/', (string) ($profile['plugins'] ?? '')), "Profil {$name} memuat plugin media.");
            $this->assertDoesNotMatchRegularExpression('/(^|\s)media(\s|$)/', (string) ($profile['toolbar'] ?? ''), "Toolbar {$name} memuat tombol media.");
        }
    }
}
