<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Menu header situs terkait Halaman Portal: Portal di kiri Beranda, Beranda di
 * kiri Berita; keduanya disembunyikan di Halaman Portal itu sendiri.
 */
class PortalHeaderNavigationTest extends TestCase
{
    /** Ambil teks menu desktop (sebelum dropdown "Tentang Kami"). */
    private function desktopMenu(string $url): array
    {
        $html = $this->get($url)->assertOk()->getContent();
        preg_match('#<div class="hidden lg:flex items-center gap-1">(.*?)Tentang Kami#s', $html, $m);
        preg_match_all('#class="nav-link[^"]*"[^>]*>\s*([^<]+?)\s*</a>#', $m[1] ?? '', $links);

        return $links[1];
    }

    /** Ambil tiga tautan teratas menu mobile (sebelum daftar menu bertingkat). */
    private function mobileMenu(string $url): array
    {
        $html = $this->get($url)->getContent();
        $start = strpos($html, '<div class="container mx-auto px-4 py-4 space-y-1">');
        preg_match_all('#rounded-lg transition-colors"[^>]*>\s*([^<]+?)\s*</a>#', substr($html, (int) $start), $links);

        return array_slice($links[1], 0, 3);
    }

    public function test_desktop_menu_order_is_portal_beranda_berita(): void
    {
        $this->assertSame(['Portal', 'Beranda', 'Berita'], $this->desktopMenu('/berita'));
    }

    public function test_mobile_menu_order_is_portal_beranda_berita(): void
    {
        $this->assertSame(['Portal', 'Beranda', 'Berita'], $this->mobileMenu('/berita'));
    }

    public function test_portal_menu_links_to_root(): void
    {
        $this->get('/tentang/kontak')->assertSee('href="' . route('portal') . '" class="nav-link"', false);
    }

    public function test_beranda_menu_is_marked_current_on_beranda(): void
    {
        $html = $this->get('/beranda')->getContent();

        $this->assertMatchesRegularExpression('#href="' . preg_quote(route('home'), '#') . '"[^>]*aria-current="page"#s', $html);
    }

    public function test_header_itself_hides_portal_and_beranda_menus_on_portal_route(): void
    {
        // Lapis kedua: bila header suatu saat dirender di route portal,
        // menu Portal & Beranda tetap tidak muncul.
        $this->get('/')->assertOk();

        $html = view('layouts.header')->render();

        $this->assertStringNotContainsString('>Portal</a>', $html);
        $this->assertStringNotContainsString('>Beranda</a>', $html);
        $this->assertStringContainsString('>Berita</a>', $html);
    }

    public function test_mobile_beranda_menu_is_highlighted_like_desktop(): void
    {
        $html = $this->get('/beranda')->getContent();
        $start = strpos($html, '<div class="container mx-auto px-4 py-4 space-y-1">');
        $mobile = substr($html, (int) $start, 3000);

        $this->assertMatchesRegularExpression('#text-school-green bg-green-50[^"]*"\s*aria-current="page"\s*>Beranda</a>#', $mobile);

        $other = $this->get('/berita')->getContent();
        $this->assertDoesNotMatchRegularExpression('#aria-current="page"\s*>Beranda</a>#', $other);
    }

    public function test_beranda_links_point_to_beranda_route(): void
    {
        $beranda = route('home');

        // Tautan berlabel "Beranda" di footer situs & halaman 404 → /beranda.
        $this->get('/berita')->assertSee('href="' . $beranda . '" class="hover:text-white transition-colors">Beranda</a>', false);
        $this->get('/halaman-yang-tidak-ada')->assertNotFound()->assertSee('href="' . $beranda . '"', false);
        $this->assertStringEndsWith('/beranda', $beranda);
    }

    public function test_portal_page_has_no_site_header_menus(): void
    {
        $this->get('/')
            ->assertDontSee('id="site-header"', false)
            ->assertDontSee('class="nav-link"', false);
    }
}
