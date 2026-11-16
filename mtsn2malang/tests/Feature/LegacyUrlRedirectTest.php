<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\News;
use Tests\TestCase;

class LegacyUrlRedirectTest extends TestCase
{
    private function makeNews(string $slug, string $status = 'published'): News
    {
        return News::create([
            'title'        => ucwords(str_replace('-', ' ', $slug)),
            'slug'         => $slug,
            'content'      => '<p>Isi berita</p>',
            'category_id'  => Category::first()->id,
            'user_id'      => $this->superAdmin()->id,
            'status'       => $status,
            'published_at' => now()->subDay(),
        ]);
    }

    public function test_old_wordpress_category_url_redirects_to_news_list(): void
    {
        // Kasus produksi: https://mtsn2kotamalang.sch.id/category/berita/ → 404
        $this->get('/category/berita/')->assertRedirect(route('berita.index'))->assertStatus(301);
    }

    public function test_known_category_keeps_its_filter_and_page(): void
    {
        $slug = Category::first()->slug;

        $this->get("/category/{$slug}/")->assertRedirect(route('berita.index', ['kategori' => $slug]));
        $this->get("/category/induk/{$slug}/page/3/")->assertRedirect(route('berita.index', ['kategori' => $slug, 'page' => 3]));
        $this->get("/category/{$slug}/feed/")->assertRedirect(route('berita.index', ['kategori' => $slug]));
    }

    public function test_old_post_permalinks_redirect_to_the_same_news(): void
    {
        $this->makeNews('juara-lomba-robotik');

        $this->get('/2024/05/10/juara-lomba-robotik/')->assertRedirect(route('berita.show', 'juara-lomba-robotik'))->assertStatus(301);
        $this->get('/2024/05/juara-lomba-robotik/')->assertRedirect(route('berita.show', 'juara-lomba-robotik'));
        $this->get('/juara-lomba-robotik/')->assertRedirect(route('berita.show', 'juara-lomba-robotik'));
    }

    public function test_draft_news_is_not_revealed_by_old_permalink(): void
    {
        $this->makeNews('berita-rahasia', 'draft');

        $this->get('/berita-rahasia/')->assertNotFound();
        $this->get('/2024/05/10/berita-rahasia/')->assertRedirect(route('search.index', ['q' => 'berita rahasia']));
    }

    public function test_archives_tags_pages_and_static_pages(): void
    {
        $this->get('/2024/05/')->assertRedirect(route('berita.index', ['tahun' => 2024, 'bulan' => 5]));
        $this->get('/2024/')->assertRedirect(route('berita.index', ['tahun' => 2024]));
        $this->get('/page/2/')->assertRedirect(route('berita.index', ['page' => 2]));
        $this->get('/tag/juara-kelas/')->assertRedirect(route('search.index', ['q' => 'juara kelas']));
        $this->get('/visi-misi/')->assertRedirect(url('tentang/visi-misi'));
        $this->get('/feed/')->assertRedirect(route('berita.index'));
        $this->get('/wp-admin/')->assertRedirect(url('/admin/login'));
    }

    public function test_unknown_urls_still_return_404(): void
    {
        $this->get('/tidak-ada-halaman-ini')->assertNotFound();
        $this->get('/berita/tidak-ada')->assertNotFound();
        $this->get('/category/../../etc/passwd')->assertNotFound();
        $this->post('/category/berita/')->assertStatus(404);
    }

    public function test_redirect_target_is_always_on_this_site(): void
    {
        foreach (['/tag/%2F%2Fevil.example/', '/category/%2F%2Fevil.example/', '/%2F%2Fevil.example/'] as $path) {
            $response = $this->get($path);
            $location = (string) $response->headers->get('Location');

            $this->assertTrue(
                $response->status() === 404 || str_starts_with($location, url('/') . '/'),
                "{$path} tidak boleh mengalihkan ke situs lain ({$response->status()} {$location})"
            );
        }
    }
}
