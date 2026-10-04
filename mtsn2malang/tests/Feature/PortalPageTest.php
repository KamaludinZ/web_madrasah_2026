<?php

namespace Tests\Feature;

use App\Models\PortalCategory;
use App\Models\PortalQuickAccess;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Halaman Portal publik di "/" — data Tab Portal dari database.
 */
class PortalPageTest extends TestCase
{
    public function test_root_shows_portal_and_beranda_moved(): void
    {
        $this->get('/')->assertOk()->assertSee('id="tab-portal"', false);
        $this->get('/beranda')->assertOk()->assertSee('hero-slider', false);
        $this->get('/portal')->assertRedirect('/');
    }

    public function test_only_active_quick_access_is_shown_in_order(): void
    {
        PortalQuickAccess::create(['name' => 'Kedua Cepat', 'url' => '/b', 'sort_order' => 2]);
        PortalQuickAccess::create(['name' => 'Pertama Cepat', 'url' => '/a', 'sort_order' => 1]);
        PortalQuickAccess::create(['name' => 'Cepat Mati', 'url' => '/c', 'is_active' => false]);

        $this->get('/')
            ->assertSeeInOrder(['Pertama Cepat', 'Kedua Cepat'])
            ->assertDontSee('Cepat Mati');
    }

    public function test_quick_access_row_is_navigation_with_external_links_in_new_tab(): void
    {
        PortalQuickAccess::create(['name' => 'Internal', 'url' => '/layanan/ppdb']);
        PortalQuickAccess::create(['name' => 'Eksternal', 'url' => 'https://ppdb.example.com']);

        $html = $this->get('/')
            ->assertSee('aria-label="Akses Cepat"', false)
            ->getContent();

        $this->assertMatchesRegularExpression('#href="/layanan/ppdb"(?!\s*target)#', $html, 'Link internal tidak boleh membuka tab baru.');
        $this->assertMatchesRegularExpression('#href="https://ppdb\.example\.com"\s+target="_blank"\s+rel="noopener"#', $html);
    }

    public function test_no_quick_access_row_when_empty(): void
    {
        $this->get('/')->assertOk()->assertDontSee('aria-label="Akses Cepat"', false);
    }

    public function test_only_active_categories_and_links_are_shown(): void
    {
        $category = PortalCategory::create(['name' => 'Kategori Hidup', 'icon' => 'shield', 'sort_order' => 1]);
        $category->links()->create(['name' => 'Link Hidup', 'url' => '/ppid/profil']);
        $category->links()->create(['name' => 'Link Mati', 'url' => '/x', 'is_active' => false]);
        PortalCategory::create(['name' => 'Kategori Mati', 'is_active' => false]);

        $this->get('/')
            ->assertSee('Kategori Hidup')
            ->assertSee('Link Hidup')
            ->assertSee('aria-haspopup="dialog"', false)
            ->assertDontSee('Link Mati')
            ->assertDontSee('Kategori Mati');
    }

    public function test_link_popup_lists_active_links_with_destination(): void
    {
        $category = PortalCategory::create(['name' => 'PPID']);
        $category->links()->create(['name' => 'Profil PPID', 'description' => 'Pejabat pengelola', 'url' => '/ppid/profil', 'sort_order' => 1]);
        $category->links()->create(['name' => 'Situs Luar', 'url' => 'https://www.example.com/x', 'sort_order' => 2]);

        $html = $this->get('/')->getContent();
        preg_match('#role="dialog" aria-modal="true" aria-labelledby="portal-category-0-title".*?</ul>#s', $html, $m);
        $popup = $m[0] ?? '';

        $this->assertNotSame('', $popup, 'Popup kategori harus dirender.');
        $this->assertStringContainsString('Profil PPID', $popup);
        $this->assertStringContainsString('Pejabat pengelola', $popup);
        $this->assertStringContainsString('/ppid/profil', $popup);
        $this->assertStringContainsString('example.com', $popup, 'Alamat tujuan eksternal ditampilkan ringkas (domain).');
        $this->assertStringContainsString('(membuka tab baru)', $popup);
        $this->assertStringContainsString('x-trap.noscroll', $popup);
    }

    public function test_category_without_links_uses_main_link(): void
    {
        PortalCategory::create(['name' => 'Berita Madrasah', 'main_link' => '/berita']);

        $this->get('/')
            ->assertSee('href="/berita"', false)
            ->assertDontSee('aria-haspopup="dialog"', false);
    }

    public function test_category_cards_have_consistent_structure_for_each_kind(): void
    {
        $withLinks = PortalCategory::create(['name' => 'Dengan Anak', 'description' => str_repeat('Deskripsi panjang. ', 30), 'sort_order' => 1]);
        $withLinks->links()->create(['name' => 'Anak', 'url' => '/a']);
        PortalCategory::create(['name' => 'Langsung', 'main_link' => '/berita', 'sort_order' => 2]);
        PortalCategory::create(['name' => 'Tanpa Tujuan', 'sort_order' => 3]);

        $html = $this->get('/')
            ->assertSeeInOrder(['Dengan Anak', 'Lihat daftar', 'Langsung', 'Tautan langsung', 'Tanpa Tujuan', 'Segera tersedia'])
            ->getContent();

        // Semua kartu memakai deskripsi 2 baris bertinggi tetap & keterangan di dasar.
        $this->assertSame(3, substr_count($html, 'line-clamp-2 min-h-[2.75rem]'));
        $this->assertSame(3, substr_count($html, 'mt-auto pt-3.5 border-t'));
    }

    public function test_empty_portal_data_shows_empty_state(): void
    {
        $this->get('/')->assertOk()->assertSee('Belum ada kategori portal.');
    }

    public function test_links_are_eager_loaded(): void
    {
        foreach (range(1, 5) as $i) {
            PortalCategory::create(['name' => "Kategori {$i}"])->links()->create(['name' => "Link {$i}", 'url' => "/l{$i}"]);
        }

        DB::enableQueryLog();
        $this->get('/')->assertOk();
        $linkQueries = collect(DB::getQueryLog())->filter(fn ($q) => str_contains($q['query'], '"portal_links"'));

        $this->assertCount(1, $linkQueries, 'Link anak harus dimuat dalam satu query (tanpa N+1).');
    }
}
