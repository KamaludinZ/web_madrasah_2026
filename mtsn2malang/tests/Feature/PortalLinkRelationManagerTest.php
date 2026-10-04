<?php

namespace Tests\Feature;

use App\Filament\Resources\PortalCategoryResource\Pages\EditPortalCategory;
use App\Filament\Resources\PortalCategoryResource\RelationManagers\LinksRelationManager;
use App\Models\PortalCategory;
use App\Models\PortalLink;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * CRUD link anak kategori Portal (relation manager di halaman edit kategori).
 */
class PortalLinkRelationManagerTest extends TestCase
{
    private PortalCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->userByEmail('editor.chief@test.local'));
        $this->category = PortalCategory::create(['name' => 'Layanan Akademik']);
    }

    private function manager()
    {
        return Livewire::test(LinksRelationManager::class, [
            'ownerRecord' => $this->category,
            'pageClass' => EditPortalCategory::class,
        ]);
    }

    public function test_edit_category_page_shows_links_table(): void
    {
        $this->category->links()->create(['name' => 'E-Learning', 'url' => 'https://example.com']);

        // Relation manager dimuat lazy oleh Filament — cukup pastikan terpasang.
        $this->get("/admin/portal-categories/{$this->category->id}/edit")
            ->assertOk()
            ->assertSeeLivewire(LinksRelationManager::class);

        $this->manager()->assertCanSeeTableRecords($this->category->links);
    }

    public function test_link_can_be_created_in_category(): void
    {
        $this->manager()
            ->callTableAction(CreateAction::class, data: [
                'name' => 'Jadwal Ujian', 'icon' => 'calendar', 'url' => '/berita',
                'description' => 'Jadwal penilaian', 'sort_order' => 2, 'is_active' => true,
            ])
            ->assertHasNoTableActionErrors();

        $link = PortalLink::firstOrFail();
        $this->assertSame($this->category->id, $link->portal_category_id);
        $this->assertSame('calendar', $link->icon);
        $this->assertSame('/berita', $link->url);
    }

    public function test_link_url_is_required_and_must_be_safe(): void
    {
        $this->manager()
            ->callTableAction(CreateAction::class, data: ['name' => 'Tanpa URL', 'icon' => 'link', 'url' => ''])
            ->assertHasTableActionErrors(['url' => 'required']);

        $this->manager()
            ->callTableAction(CreateAction::class, data: ['name' => 'Jahat', 'icon' => 'link', 'url' => 'javascript:alert(1)'])
            ->assertHasTableActionErrors(['url']);

        $this->assertDatabaseCount('portal_links', 0);
    }

    public function test_link_can_be_edited_and_deleted(): void
    {
        $link = $this->category->links()->create(['name' => 'Lama', 'url' => '/a']);

        $this->manager()
            ->callTableAction(EditAction::class, $link, data: ['name' => 'Baru', 'icon' => 'star', 'url' => '/b', 'is_active' => false])
            ->assertHasNoTableActionErrors();

        $link->refresh();
        $this->assertSame('Baru', $link->name);
        $this->assertSame('/b', $link->url);
        $this->assertFalse($link->is_active);

        $this->manager()->callTableAction(DeleteAction::class, $link);
        $this->assertModelMissing($link);
    }

    public function test_unknown_icon_is_rejected_by_server(): void
    {
        $this->manager()
            ->callTableAction(CreateAction::class, data: ['name' => 'X', 'icon' => 'bukan-ikon', 'url' => '/a'])
            ->assertHasTableActionErrors(['icon']);

        $this->assertDatabaseCount('portal_links', 0);
    }

    public function test_links_table_shows_icon_preview(): void
    {
        $this->category->links()->create(['name' => 'Berikon', 'icon' => 'calendar', 'url' => '/a']);

        $this->manager()
            ->assertTableColumnExists('icon')
            ->assertSee(\App\Support\PortalIcons::path('calendar'), false);
    }

    public function test_links_only_show_for_their_own_category(): void
    {
        $own = $this->category->links()->create(['name' => 'Milik sendiri', 'url' => '/a']);
        $other = PortalCategory::create(['name' => 'Lain'])->links()->create(['name' => 'Milik lain', 'url' => '/b']);

        $this->manager()
            ->assertCanSeeTableRecords([$own])
            ->assertCanNotSeeTableRecords([$other]);
    }
}
