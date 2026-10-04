<?php

namespace Tests\Feature;

use App\Models\PortalCategory;
use App\Models\PortalQuickAccess;
use App\Services\PortalDataService;
use Tests\TestCase;

/**
 * Data publik Tab Portal: kategori/link/Akses Cepat aktif, terurut.
 */
class PortalDataServiceTest extends TestCase
{
    private function service(): PortalDataService
    {
        return app(PortalDataService::class);
    }

    public function test_active_categories_are_ordered_and_exclude_inactive(): void
    {
        PortalCategory::create(['name' => 'C', 'sort_order' => 3]);
        PortalCategory::create(['name' => 'A', 'sort_order' => 1]);
        PortalCategory::create(['name' => 'Mati', 'sort_order' => 0, 'is_active' => false]);
        PortalCategory::create(['name' => 'B', 'sort_order' => 2]);

        $this->assertSame(['A', 'B', 'C'], array_column($this->service()->activeCategories(), 'name'));
    }

    public function test_category_shape_and_only_active_links_in_order(): void
    {
        $category = PortalCategory::create(['name' => 'PPID', 'icon' => 'shield', 'description' => 'Info publik', 'main_link' => '/ppid/profil']);
        $category->links()->create(['name' => 'Kedua', 'url' => '/b', 'sort_order' => 2]);
        $category->links()->create(['name' => 'Pertama', 'url' => '/a', 'sort_order' => 1, 'description' => 'D']);
        $category->links()->create(['name' => 'Mati', 'url' => '/x', 'is_active' => false]);

        $data = $this->service()->activeCategories()[0];

        $this->assertSame(['name', 'icon', 'description', 'mainLink', 'isActive', 'links'], array_keys($data));
        $this->assertSame('/ppid/profil', $data['mainLink']);
        $this->assertSame(['Pertama', 'Kedua'], array_column($data['links'], 'name'));
        $this->assertSame(['name' => 'Pertama', 'icon' => 'link', 'description' => 'D', 'url' => '/a', 'isActive' => true], $data['links'][0]);
    }

    public function test_quick_access_is_active_and_ordered(): void
    {
        PortalQuickAccess::create(['name' => 'Dua', 'url' => '/2', 'sort_order' => 2]);
        PortalQuickAccess::create(['name' => 'Satu', 'url' => '/1', 'sort_order' => 1]);
        PortalQuickAccess::create(['name' => 'Mati', 'url' => '/x', 'is_active' => false]);

        $items = $this->service()->activeQuickAccess();

        $this->assertSame(['Satu', 'Dua'], array_column($items, 'name'));
        $this->assertSame(['name', 'icon', 'url', 'isActive'], array_keys($items[0]));
    }

    public function test_empty_tables_return_empty_arrays(): void
    {
        $this->assertSame([], $this->service()->activeCategories());
        $this->assertSame([], $this->service()->activeQuickAccess());
    }
}
