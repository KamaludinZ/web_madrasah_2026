<?php

namespace Tests\Feature;

use App\Filament\Resources\MediaResource;
use App\Filament\Resources\NewsResource;
use Filament\Facades\Filament;
use Tests\TestCase;

/**
 * Kontributor tidak boleh membuka halaman daftar/tambah/ubah menu admin lain
 * dengan mengetik URL langsung (bukan hanya menu yang disembunyikan).
 */
class ResourceUrlAccessTest extends TestCase
{
    public function test_contributor_is_forbidden_on_every_other_resource_page(): void
    {
        $user = $this->userByEmail('contributor@test.local');
        $checked = 0;

        foreach (Filament::getPanel('admin')->getResources() as $resource) {
            if (in_array($resource, [NewsResource::class, MediaResource::class], true)) {
                continue;
            }

            foreach (array_keys($resource::getPages()) as $page) {
                $params = [];
                if (! in_array($page, ['index', 'create'], true)) {
                    $record = $resource::getModel()::query()->first();
                    if (! $record) {
                        continue;
                    }
                    $params = ['record' => $record->getRouteKey()];
                }

                $this->actingAs($user)->get($resource::getUrl($page, $params))
                    ->assertForbidden();
                $checked++;
            }
        }

        $this->assertGreaterThan(40, $checked);
    }
}
