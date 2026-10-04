<?php

namespace Tests\Feature;

use App\Filament\Resources\PortalCategoryResource\Pages\CreatePortalCategory;
use App\Filament\Resources\PortalCategoryResource\Pages\EditPortalCategory;
use App\Filament\Resources\PortalCategoryResource\Pages\ListPortalCategories;
use App\Models\PortalCategory;
use App\Rules\SafeLink;
use Illuminate\Support\Facades\Validator;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * CRUD kategori Portal di panel admin (menu Portal → Kategori Portal).
 */
class PortalCategoryResourceTest extends TestCase
{
    // Satu role per method: Filament menyimpan navigasi per proses (lihat
    // catatan di RoleAccessTest), sehingga beberapa role dalam satu method
    // saling bocor.

    public function test_super_admin_can_open_category_pages(): void
    {
        $this->actingAs($this->superAdmin())->get('/admin/portal-categories')->assertOk();
        $this->actingAs($this->superAdmin())->get('/admin/portal-categories/create')->assertOk();
    }

    public function test_editor_in_chief_can_open_category_pages(): void
    {
        $user = $this->userByEmail('editor.chief@test.local');
        $this->actingAs($user)->get('/admin/portal-categories')->assertOk();
        $this->actingAs($user)->get('/admin/portal-categories/create')->assertOk();
    }

    public function test_editor_cannot_open_category_pages(): void
    {
        $this->actingAs($this->userByEmail('editor@test.local'))->get('/admin/portal-categories')->assertForbidden();
    }

    public function test_moderator_cannot_open_category_pages(): void
    {
        $this->actingAs($this->userByEmail('moderator@test.local'))->get('/admin/portal-categories')->assertForbidden();
    }

    public function test_contributor_cannot_open_category_pages(): void
    {
        $this->actingAs($this->userByEmail('contributor@test.local'))->get('/admin/portal-categories')->assertForbidden();
    }

    public function test_category_can_be_created_edited_and_deleted(): void
    {
        $this->actingAs($this->userByEmail('editor.chief@test.local'));

        Livewire::test(CreatePortalCategory::class)
            ->fillForm(['name' => 'Layanan Akademik', 'icon' => 'academic-cap', 'description' => 'Deskripsi', 'main_link' => '/berita', 'sort_order' => 3])
            ->call('create')
            ->assertHasNoFormErrors();

        $category = PortalCategory::where('name', 'Layanan Akademik')->firstOrFail();
        $this->assertSame('academic-cap', $category->icon);
        $this->assertSame('/berita', $category->main_link);
        $this->assertSame(3, $category->sort_order);
        $this->assertTrue($category->is_active);

        Livewire::test(EditPortalCategory::class, ['record' => $category->getRouteKey()])
            ->fillForm(['name' => 'Layanan Akademik Baru', 'is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Layanan Akademik Baru', $category->fresh()->name);
        $this->assertFalse($category->fresh()->is_active);

        Livewire::test(EditPortalCategory::class, ['record' => $category->getRouteKey()])->callAction('delete');
        $this->assertModelMissing($category);
    }

    public function test_deleting_category_also_deletes_its_links(): void
    {
        $category = PortalCategory::create(['name' => 'Induk']);
        $category->links()->create(['name' => 'Anak', 'url' => '/a']);

        $category->delete();

        $this->assertDatabaseCount('portal_links', 0);
    }

    public function test_name_and_icon_are_required(): void
    {
        $this->actingAs($this->superAdmin());

        Livewire::test(CreatePortalCategory::class)
            ->fillForm(['name' => '', 'icon' => null])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required', 'icon' => 'required']);
    }

    public function test_unsafe_main_link_is_rejected(): void
    {
        $this->actingAs($this->superAdmin());

        foreach (['javascript:alert(1)', 'data:text/html,x', '//evil.example', 'ppid/profil'] as $link) {
            Livewire::test(CreatePortalCategory::class)
                ->fillForm(['name' => 'Uji', 'icon' => 'link', 'main_link' => $link])
                ->call('create')
                ->assertHasFormErrors(['main_link']);
        }

        $this->assertDatabaseCount('portal_categories', 0);
    }

    public function test_safe_link_rule(): void
    {
        $passes = fn ($value) => Validator::make(['l' => $value], ['l' => [new SafeLink()]])->passes();

        $this->assertTrue($passes('/ppid/profil'));
        $this->assertTrue($passes('https://example.com/a?b=1'));
        $this->assertTrue($passes(null));
        $this->assertFalse($passes('javascript:alert(1)'));
        $this->assertFalse($passes('http://contoh spasi.com'));
        $this->assertFalse($passes('//evil.example'));
    }

    public function test_unknown_icon_is_rejected_by_server(): void
    {
        $this->actingAs($this->superAdmin());

        Livewire::test(CreatePortalCategory::class)
            ->fillForm(['name' => 'Uji', 'icon' => 'tidak-ada"><script>x</script>'])
            ->call('create')
            ->assertHasFormErrors(['icon']);

        $this->assertDatabaseCount('portal_categories', 0);
    }

    public function test_negative_sort_order_is_rejected(): void
    {
        $this->actingAs($this->superAdmin());

        Livewire::test(CreatePortalCategory::class)
            ->fillForm(['name' => 'Uji', 'icon' => 'link', 'sort_order' => -1])
            ->call('create')
            ->assertHasFormErrors(['sort_order']);
    }

    public function test_icon_options_show_preview_and_escape_labels(): void
    {
        $options = \App\Support\PortalIcons::htmlOptions();

        $this->assertCount(count(\App\Support\PortalIcons::names()), $options);
        $this->assertStringContainsString('<svg', $options['shield']);
        $this->assertStringContainsString('Keamanan / Perlindungan', $options['shield']);
    }

    public function test_list_shows_icon_preview_column(): void
    {
        $this->actingAs($this->superAdmin());
        PortalCategory::create(['name' => 'Berikon', 'icon' => 'star']);

        Livewire::test(ListPortalCategories::class)
            ->assertTableColumnExists('icon')
            ->assertSee(\App\Support\PortalIcons::path('star'), false);
    }

    public function test_list_page_shows_categories_in_sort_order(): void
    {
        $this->actingAs($this->superAdmin());
        $b = PortalCategory::create(['name' => 'Kedua', 'sort_order' => 2]);
        $a = PortalCategory::create(['name' => 'Pertama', 'sort_order' => 1]);

        Livewire::test(ListPortalCategories::class)->assertCanSeeTableRecords([$a, $b], inOrder: true);
    }
}
