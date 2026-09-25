<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Semua tes memakai database KHUSUS UJI (lihat DB_DATABASE di phpunit.xml)
 * yang dikosongkan ulang otomatis. Tes menolak berjalan bila nama database
 * tidak berakhiran "_testing" — mencegah data asli (produksi/dev) terhapus.
 */
abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /** Jalankan DatabaseSeeder (role, super admin, setting default) sekali. */
    protected bool $seed = true;

    public const ROLE_USERS = [
        'contributor@test.local' => 'contributor',
        'moderator@test.local' => 'moderator',
        'editor@test.local' => 'editor',
        'editor.chief@test.local' => 'editor_in_chief',
    ];

    public const TEST_PASSWORD = 'password123';

    protected function beforeRefreshingDatabase(): void
    {
        $connection = config('database.default');
        $database = (string) config("database.connections.{$connection}.database");

        if (! str_ends_with($database, '_testing')) {
            throw new RuntimeException(
                "Tes dibatalkan: database \"{$database}\" bukan database uji. " .
                'Gunakan database berakhiran "_testing" (lihat phpunit.xml).'
            );
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::ROLE_USERS as $email => $role) {
            User::create([
                'name' => ucwords(str_replace(['@test.local', '.', '_'], ['', ' ', ' '], $email)),
                'email' => $email,
                'password' => Hash::make(self::TEST_PASSWORD),
                'is_active' => true,
            ])->assignRole($role);
        }

        User::where('email', 'admin@mtsn2kotamalang.sch.id')->update(['password' => Hash::make(self::TEST_PASSWORD)]);
    }

    protected function userByEmail(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    protected function superAdmin(): User
    {
        return $this->userByEmail('admin@mtsn2kotamalang.sch.id');
    }
}
