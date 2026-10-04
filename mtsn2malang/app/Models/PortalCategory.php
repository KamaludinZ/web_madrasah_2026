<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Kategori pada Tab Portal. Punya link anak (popup daftar link) atau, bila
 * tanpa anak, langsung menuju main_link.
 */
class PortalCategory extends Model
{
    protected $fillable = [
        'name',
        'icon',
        'description',
        'main_link',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function links(): HasMany
    {
        return $this->hasMany(PortalLink::class)->orderBy('sort_order')->orderBy('id');
    }

    public function activeLinks(): HasMany
    {
        return $this->links()->where('is_active', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
