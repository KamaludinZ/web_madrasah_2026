<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BadgeAchievement extends Model
{
    protected $fillable = ['name', 'logo', 'order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) return null;
        return asset('storage/' . $this->logo);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order');
    }
}
