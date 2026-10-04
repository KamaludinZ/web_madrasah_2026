<?php

namespace App\Services;

use App\Models\PortalCategory;
use App\Models\PortalLink;
use App\Models\PortalQuickAccess;

/**
 * Data Tab Portal untuk pengunjung: hanya item berstatus aktif, terurut
 * (sort_order lalu id). Bentuk array sama dengan yang dipakai view
 * pages.portal dan komponen x-portal-*.
 */
class PortalDataService
{
    /**
     * Kategori aktif beserta link anak yang aktif saja (eager load: satu
     * query untuk seluruh link, tanpa N+1).
     *
     * @return array<int, array{name: string, icon: string, description: ?string, mainLink: ?string, isActive: bool, links: array}>
     */
    public function activeCategories(): array
    {
        return PortalCategory::active()->ordered()
            ->with(['activeLinks'])
            ->get()
            ->map(fn (PortalCategory $category) => [
                'name'        => $category->name,
                'icon'        => $category->icon,
                'description' => $category->description,
                'mainLink'    => $category->main_link,
                'isActive'    => true,
                'links'       => $category->activeLinks->map(fn (PortalLink $link) => $this->linkData($link))->all(),
            ])
            ->all();
    }

    /**
     * Tombol Akses Cepat aktif, terurut.
     *
     * @return array<int, array{name: string, icon: string, url: string, isActive: bool}>
     */
    public function activeQuickAccess(): array
    {
        return PortalQuickAccess::active()->ordered()->get()
            ->map(fn (PortalQuickAccess $item) => [
                'name'     => $item->name,
                'icon'     => $item->icon,
                'url'      => $item->url,
                'isActive' => true,
            ])
            ->all();
    }

    /** @return array{name: string, icon: string, description: ?string, url: string, isActive: bool} */
    private function linkData(PortalLink $link): array
    {
        return [
            'name'        => $link->name,
            'icon'        => $link->icon,
            'description' => $link->description,
            'url'         => $link->url,
            'isActive'    => true,
        ];
    }
}
