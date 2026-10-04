<?php

namespace Tests\Feature;

use App\Filament\Resources\PortalQuickAccessResource\Pages\CreatePortalQuickAccess;
use App\Filament\Resources\PortalQuickAccessResource\Pages\EditPortalQuickAccess;
use App\Filament\Resources\PortalQuickAccessResource\Pages\ListPortalQuickAccesses;
use App\Models\PortalQuickAccess;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * CRUD tombol Akses Cepat Portal (menu Portal → Akses Cepat).
 * Satu role per method (lihat catatan di RoleAccessTest).
 */
class PortalQuickAccessResourceTest extends TestCase
{
    public function test_editor_in_chief_can_open_pages(): void
    {
        $user = $this->userByEmail('editor.chief@test.local');
        $this->actingAs($user)->get('/admin/portal-quick-access')->assertOk();
        $this->actingAs($user)->get('/admin/portal-quick-access/create')->assertOk();
    }

    public function test_editor_cannot_open_pages(): void
    {
        $this->actingAs($this->userByEmail('editor@test.local'))->get('/admin/portal-quick-access')->assertForbidden();
    }

    public function test_contributor_cannot_open_pages(): void
    {
        $this->actingAs($this->userByEmail('contributor@test.local'))->get('/admin/portal-quick-access')->assertForbidden();
    }

    public function test_quick_access_can_be_created_edited_and_deleted(): void
    {
        $this->actingAs($this->superAdmin());

        Livewire::test(CreatePortalQuickAccess::class)
            ->fillForm(['name' => 'Info PPDB', 'icon' => 'academic-cap', 'url' => '/layanan/ppdb', 'sort_order' => 1])
            ->call('create')
            ->assertHasNoFormErrors();

        $item = PortalQuickAccess::where('name', 'Info PPDB')->firstOrFail();
        $this->assertSame('/layanan/ppdb', $item->url);
        $this->assertTrue($item->is_active);

        Livewire::test(EditPortalQuickAccess::class, ['record' => $item->getRouteKey()])
            ->fillForm(['name' => 'PPDB Online', 'url' => 'https://ppdb.example.com', 'is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $item->refresh();
        $this->assertSame('PPDB Online', $item->name);
        $this->assertFalse($item->is_active);

        Livewire::test(EditPortalQuickAccess::class, ['record' => $item->getRouteKey()])->callAction('delete');
        $this->assertModelMissing($item);
    }

    public function test_url_is_required_and_must_be_safe(): void
    {
        $this->actingAs($this->superAdmin());

        Livewire::test(CreatePortalQuickAccess::class)
            ->fillForm(['name' => 'X', 'icon' => 'link', 'url' => ''])
            ->call('create')
            ->assertHasFormErrors(['url' => 'required']);

        Livewire::test(CreatePortalQuickAccess::class)
            ->fillForm(['name' => 'X', 'icon' => 'link', 'url' => 'javascript:alert(1)'])
            ->call('create')
            ->assertHasFormErrors(['url']);

        $this->assertDatabaseCount('portal_quick_access', 0);
    }

    public function test_unknown_icon_is_rejected_by_server(): void
    {
        $this->actingAs($this->superAdmin());

        Livewire::test(CreatePortalQuickAccess::class)
            ->fillForm(['name' => 'X', 'icon' => 'bukan-ikon', 'url' => '/a'])
            ->call('create')
            ->assertHasFormErrors(['icon']);
    }

    public function test_list_shows_icon_preview(): void
    {
        $this->actingAs($this->superAdmin());
        PortalQuickAccess::create(['name' => 'PPDB', 'icon' => 'academic-cap', 'url' => '/a']);

        Livewire::test(ListPortalQuickAccesses::class)
            ->assertTableColumnExists('icon')
            ->assertSee(\App\Support\PortalIcons::path('academic-cap'), false);
    }

    public function test_list_is_sorted_by_sort_order(): void
    {
        $this->actingAs($this->superAdmin());
        $b = PortalQuickAccess::create(['name' => 'B', 'url' => '/b', 'sort_order' => 2]);
        $a = PortalQuickAccess::create(['name' => 'A', 'url' => '/a', 'sort_order' => 1]);

        Livewire::test(ListPortalQuickAccesses::class)->assertCanSeeTableRecords([$a, $b], inOrder: true);
    }
}
