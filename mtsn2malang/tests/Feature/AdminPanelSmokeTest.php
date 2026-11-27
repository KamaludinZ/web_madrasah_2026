<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\News;
use App\Models\Saran;
use Filament\Facades\Filament;
use Tests\TestCase;

/**
 * Membuka SETIAP halaman panel admin (daftar, tambah, edit, halaman kustom,
 * dashboard) sebagai super admin dan memastikan tidak ada error. Penjaga
 * utama saat upgrade versi Filament/Livewire.
 */
class AdminPanelSmokeTest extends TestCase
{
    private function seedSampleRecords(): void
    {
        $admin = $this->superAdmin();

        News::create([
            'title' => 'Berita Uji Panel', 'slug' => 'berita-uji-panel', 'content' => '<p>Isi</p>',
            'category_id' => Category::first()->id, 'user_id' => $admin->id,
            'status' => 'published', 'published_at' => now(),
        ]);
        Complaint::create(['type' => 'masyarakat', 'name' => 'Budi', 'subject' => 'Uji', 'content' => 'Isi pengaduan uji']);
        Saran::create(['name' => 'Siti', 'subject' => 'Uji saran', 'content' => 'Isi saran uji panel', 'status' => 'pending']);
    }

    public function test_every_admin_page_renders_for_super_admin(): void
    {
        $this->seedSampleRecords();
        $this->actingAs($this->superAdmin());

        $panel = Filament::getPanel('admin');
        Filament::setCurrentPanel($panel);

        $urls = [$panel->getUrl()];

        foreach ($panel->getPages() as $page) {
            $urls[] = $page::getUrl();
        }

        foreach ($panel->getResources() as $resource) {
            $pages = $resource::getPages();

            foreach (['index', 'create'] as $name) {
                if (isset($pages[$name])) {
                    $urls[] = $resource::getUrl($name);
                }
            }

            $record = $resource::getEloquentQuery()->first();
            foreach (['edit', 'view'] as $name) {
                if ($record && isset($pages[$name])) {
                    $urls[] = $resource::getUrl($name, ['record' => $record]);
                }
            }
        }

        $failures = [];
        foreach (array_unique($urls) as $url) {
            $response = $this->get($url);
            if ($response->status() !== 200) {
                $failures[] = $response->status() . ' ' . $url . ' ' . mb_substr(strip_tags((string) ($response->exception?->getMessage() ?? '')), 0, 200);
            }
        }

        $this->assertGreaterThan(60, count($urls), 'Daftar halaman admin terlalu sedikit — penemuan halaman gagal?');
        $this->assertSame([], $failures, "Halaman admin gagal dibuka:\n" . implode("\n", $failures));
    }
}
