<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Menjaga docker/nginx/site.conf tetap cocok dengan alamat aset Livewire.
 * Kasus produksi: setelah upgrade ke Livewire 4 skrip pindah ke
 * /livewire-{hash}/livewire.min.js, tertangkap aturan file statis .js →
 * 404 HTML → form login admin & kode keamanan tidak berfungsi.
 */
class NginxConfigTest extends TestCase
{
    /** @return array<int, array{pattern: string, offset: int}> lokasi regex sesuai urutan di file */
    private function regexLocations(): array
    {
        $conf = file_get_contents(base_path('docker/nginx/site.conf'));
        preg_match_all('/^\s*location\s+(~\*?)\s+(\S+)\s*\{/m', $conf, $m, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        return array_map(fn ($x) => [
            'pattern' => '#' . str_replace('#', '\#', $x[2][0]) . '#' . ($x[1][0] === '~*' ? 'i' : ''),
            'offset'  => $x[0][1],
        ], $m);
    }

    /** nginx memakai lokasi regex PERTAMA yang cocok. */
    private function firstMatchingLocation(string $uri): ?string
    {
        foreach ($this->regexLocations() as $location) {
            if (preg_match($location['pattern'], $uri)) {
                return $location['pattern'];
            }
        }

        return null;
    }

    public function test_livewire_script_is_routed_to_laravel_not_static_404(): void
    {
        $html = $this->get('/admin/login')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#/livewire-[0-9a-f]+/livewire(?:\.min)?\.js#', $html);
        preg_match('#src="https?://[^/"]+(/livewire[^"?]*)#', $html, $m);

        foreach ([$m[1], '/livewire-831234ab/preview-file/foto.png', '/livewire/livewire.min.js'] as $uri) {
            $location = $this->firstMatchingLocation($uri);
            $this->assertNotNull($location, $uri);
            $this->assertStringContainsString('livewire', $location, "{$uri} tertangkap aturan lain: {$location}");
        }

        // Skrip benar-benar dilayani Laravel sebagai JavaScript.
        $this->get($m[1])->assertOk()->assertHeader('Content-Type', 'application/javascript; charset=utf-8');
    }

    public function test_missing_static_files_still_answered_by_nginx(): void
    {
        $location = $this->firstMatchingLocation('/images/tidak-ada.png');

        $this->assertNotNull($location);
        $this->assertStringNotContainsString('livewire', $location);
    }
}
