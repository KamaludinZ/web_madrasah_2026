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
        'guzzlehttp/guzzle'           => '7.15.2',
        'guzzlehttp/psr7'             => '2.12.3',
        'mtdowling/jmespath.php'      => '2.9.0',
        'spatie/laravel-medialibrary' => '11.23.0',
        'ueberdosis/tiptap-php'       => '2.2.0',
        'tinymce/tinymce'             => '8.9.3', // XSS plugin media (GHSA-mf2p-h6hf-fcwm)
        'filament/filament'           => '5.0.0',
        'livewire/livewire'           => '4.4.9',
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

    public function test_self_hosted_tinymce_matches_the_patched_composer_package(): void
    {
        // Editor memuat TinyMCE dari public/vendor/tinymce (provider vendor);
        // file itu harus sama dengan paket composer yang sudah ditambal, bukan
        // salinan lama yang tertinggal.
        $published = json_decode((string) file_get_contents(public_path('vendor/tinymce/package.json')), true)['version'] ?? null;

        $this->assertSame(InstalledVersions::getPrettyVersion('tinymce/tinymce'), $published,
            'Jalankan: php artisan vendor:publish --provider="AmidEsfahani\\FilamentTinyEditor\\TinyeditorServiceProvider" --tag=public --force');
        $this->assertSame('vendor', config('filament-tinyeditor.provider'));
        $this->assertSame($published, config('filament-tinyeditor.version.tiny'));
    }
}
