<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Menu admin "Portal": Akses Cepat → Kategori Portal → Pengaturan Portal.
 * Satu role per method (lihat catatan di RoleAccessTest).
 */
class PortalAdminNavigationTest extends TestCase
{
    private function navGroup(string $html, string $group): array
    {
        // Label grup lalu item-item di dalamnya sampai grup berikutnya.
        $start = strpos($html, '>' . $group . '<');
        if ($start === false) {
            preg_match('#fi-sidebar-group-label[^>]*>\s*' . preg_quote($group, '#') . '\s*<#', $html, $m, PREG_OFFSET_CAPTURE);
            $start = $m[0][1] ?? false;
        }
        $this->assertNotFalse($start, "Grup navigasi \"{$group}\" tidak ditemukan.");

        $rest = substr($html, $start);
        $end = strpos($rest, 'fi-sidebar-group-label', 40);
        preg_match_all('/class="fi-sidebar-item-label[^"]*"[^>]*>([^<]+)</', $end ? substr($rest, 0, $end) : $rest, $items);

        return array_map(fn ($s) => html_entity_decode(trim($s)), $items[1]);
    }

    public function test_super_admin_sees_portal_group_with_three_menus_in_order(): void
    {
        $html = $this->actingAs($this->superAdmin())->get('/admin')->getContent();

        $this->assertSame(['Akses Cepat', 'Kategori Portal', 'Pengaturan Portal'], $this->navGroup($html, 'Portal'));
    }

    public function test_all_portal_admin_pages_open_for_editor_in_chief(): void
    {
        $user = $this->userByEmail('editor.chief@test.local');

        foreach (['/admin/portal-quick-access', '/admin/portal-categories', '/admin/manage-portal'] as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    public function test_contributor_does_not_see_portal_group(): void
    {
        $html = $this->actingAs($this->userByEmail('contributor@test.local'))->get('/admin')->getContent();

        $this->assertStringNotContainsString('Kategori Portal', $html);
        $this->assertStringNotContainsString('Pengaturan Portal', $html);
    }
}
