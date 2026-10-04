<?php

namespace Tests\Feature;

use App\Filament\Resources\PortalCategoryResource\Pages\EditPortalCategory;
use App\Filament\Resources\PortalCategoryResource\Pages\ListPortalCategories;
use App\Filament\Resources\PortalCategoryResource\RelationManagers\LinksRelationManager;
use App\Filament\Resources\PortalQuickAccessResource\Pages\ListPortalQuickAccesses;
use App\Models\PortalCategory;
use App\Models\PortalQuickAccess;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Atur urutan dengan seret (reorder tabel Filament) — tersimpan ke sort_order
 * dan langsung berlaku di Halaman Portal.
 */
class PortalOrderingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->superAdmin());
    }

    public function test_reordering_categories_updates_sort_order_and_portal(): void
    {
        $a = PortalCategory::create(['name' => 'Kategori A', 'sort_order' => 1]);
        $b = PortalCategory::create(['name' => 'Kategori B', 'sort_order' => 2]);
        $c = PortalCategory::create(['name' => 'Kategori C', 'sort_order' => 3]);

        Livewire::test(ListPortalCategories::class)
            ->call('reorderTable', [(string) $c->id, (string) $a->id, (string) $b->id]);

        $this->assertSame([1, 2, 3], [$c->fresh()->sort_order, $a->fresh()->sort_order, $b->fresh()->sort_order]);
        $this->get('/')->assertSeeInOrder(['Kategori C', 'Kategori A', 'Kategori B']);
    }

    public function test_reordering_quick_access_updates_portal(): void
    {
        $a = PortalQuickAccess::create(['name' => 'Cepat A', 'url' => '/a', 'sort_order' => 1]);
        $b = PortalQuickAccess::create(['name' => 'Cepat B', 'url' => '/b', 'sort_order' => 2]);

        Livewire::test(ListPortalQuickAccesses::class)
            ->call('reorderTable', [(string) $b->id, (string) $a->id]);

        $this->get('/')->assertSeeInOrder(['Cepat B', 'Cepat A']);
    }

    public function test_reordering_links_within_category(): void
    {
        $category = PortalCategory::create(['name' => 'Induk']);
        $x = $category->links()->create(['name' => 'Link X', 'url' => '/x', 'sort_order' => 1]);
        $y = $category->links()->create(['name' => 'Link Y', 'url' => '/y', 'sort_order' => 2]);

        Livewire::test(LinksRelationManager::class, ['ownerRecord' => $category, 'pageClass' => EditPortalCategory::class])
            ->call('reorderTable', [(string) $y->id, (string) $x->id]);

        $this->assertSame(['Link Y', 'Link X'], $category->fresh()->links->pluck('name')->all());
    }
}
