<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

/**
 * Regression test untuk matriks akses role di panel admin: memastikan tiap
 * role hanya melihat menu navigasi & bisa membuka halaman yang memang
 * diizinkan oleh canViewAny()/canAccess() masing-masing resource/page.
 *
 * Tiap role WAJIB di method test terpisah — Filament menyimpan navigasi
 * yang sudah di-resolve dalam singleton per-proses PHP, jadi memeriksa
 * beberapa role dalam satu method akan membocorkan hasil role sebelumnya
 * (TestCase me-refresh application container di setUp() tiap method).
 *
 * actingAs() tidak melalui form login/password/captcha. Read-only GET,
 * tanpa RefreshDatabase — tidak menulis/menghapus data.
 */
class RoleAccessTest extends TestCase
{
    /** Resource super_admin-only yang TIDAK BOLEH muncul di nav role lain. */
    private const SUPER_ADMIN_ONLY_NAV = ['Pengguna', 'Pengaturan Website', 'Mode Maintenance', 'Migrasi WordPress'];

    private function navOf(User $user): array
    {
        $html = $this->actingAs($user)->get('/admin')->getContent();
        preg_match_all('/class="fi-sidebar-item-label[^"]*"[^>]*>([^<]+)</', $html, $m);

        return array_values(array_unique(array_map(fn ($s) => html_entity_decode(trim($s)), $m[1])));
    }

    public function test_super_admin_sees_full_navigation(): void
    {
        $user = User::where('email', 'admin@mtsn2kotamalang.sch.id')->firstOrFail();
        $nav = $this->navOf($user);

        foreach (self::SUPER_ADMIN_ONLY_NAV as $item) {
            $this->assertContains($item, $nav, "Super admin harus melihat menu \"{$item}\".");
        }

        $this->actingAs($user)->get('/admin/users')->assertOk();
        $this->actingAs($user)->get('/admin/manage-settings')->assertOk();
    }

    public function test_contributor_only_sees_news_and_media(): void
    {
        $user = User::where('email', 'contributor@test.local')->firstOrFail();
        $nav = $this->navOf($user);

        $this->assertEqualsCanonicalizing(['Dashboard', 'Berita', 'Media'], $nav);

        $this->actingAs($user)->get('/admin/news')->assertOk();
        $this->actingAs($user)->get('/admin/media')->assertOk();

        foreach (['/admin/users', '/admin/manage-settings', '/admin/complaints', '/admin/banners'] as $blocked) {
            $this->actingAs($user)->get($blocked)->assertForbidden();
        }
    }

    public function test_moderator_only_sees_moderation_resources(): void
    {
        $user = User::where('email', 'moderator@test.local')->firstOrFail();
        $nav = $this->navOf($user);

        $this->assertEqualsCanonicalizing(
            ['Dashboard', 'Berita', 'Media', 'Moderasi Komentar', 'Popup & Pengumuman', 'Whistleblowing & Pengaduan', 'Link Survei', 'Saran'],
            $nav
        );

        foreach (self::SUPER_ADMIN_ONLY_NAV as $item) {
            $this->assertNotContains($item, $nav, "Moderator tidak boleh melihat menu \"{$item}\".");
        }

        $this->actingAs($user)->get('/admin/users')->assertForbidden();
        $this->actingAs($user)->get('/admin/banners')->assertForbidden();
    }

    public function test_editor_cannot_reach_super_admin_only_pages(): void
    {
        $user = User::where('email', 'editor@test.local')->firstOrFail();
        $nav = $this->navOf($user);

        foreach (self::SUPER_ADMIN_ONLY_NAV as $item) {
            $this->assertNotContains($item, $nav, "Editor tidak boleh melihat menu \"{$item}\".");
        }

        $this->actingAs($user)->get('/admin/news')->assertOk();

        foreach (['/admin/users', '/admin/manage-settings', '/admin/manage-maintenance', '/admin/manage-wordpress-migration', '/admin/complaints', '/admin/subscribers'] as $blocked) {
            $this->actingAs($user)->get($blocked)->assertForbidden();
        }
    }

    public function test_editor_in_chief_sees_everything_except_super_admin_only_pages(): void
    {
        $user = User::where('email', 'editor.chief@test.local')->firstOrFail();
        $nav = $this->navOf($user);

        foreach (self::SUPER_ADMIN_ONLY_NAV as $item) {
            $this->assertNotContains($item, $nav, "Editor in Chief tidak boleh melihat menu \"{$item}\".");
        }

        $this->actingAs($user)->get('/admin/banners')->assertOk();
        $this->actingAs($user)->get('/admin/complaints')->assertOk();

        foreach (['/admin/users', '/admin/manage-settings', '/admin/manage-maintenance', '/admin/manage-wordpress-migration'] as $blocked) {
            $this->actingAs($user)->get($blocked)->assertForbidden();
        }
    }
}
